<?php

namespace Tests\Feature;

use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AssessmentJourneyFixtures;
use Tests\TestCase;

/**
 * Q-04 Phase 9: the complete student journey end to end.
 *
 * Every step is a real HTTP request following the user's actual path:
 * register, start, answer, rate, complete, then read the result and the
 * history. No internal service is called directly and no row is inserted by
 * the test once the journey has started.
 */
class CompleteJourneyIntegrationTest extends TestCase
{
    use AssessmentJourneyFixtures;
    use RefreshDatabase;

    private const CODES = ['R', 'I', 'A', 'S', 'E', 'C'];

    private AssessmentVersion $version;

    protected function setUp(): void
    {
        parent::setUp();

        $this->version = $this->createPublishedAssessmentVersion(1);
    }

    /**
     * The full happy path in one test: the transitions must all succeed and
     * the result must be persisted by the time completion returns.
     */
    public function test_a_student_can_complete_the_whole_journey(): void
    {
        $email = 'journey'.uniqid().'@example.com';

        // 1. Register as a student.
        $this->post('/register', [
            'name' => 'طالب الرحلة',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));

        $student = User::where('email', $email)->firstOrFail();
        $this->assertSame('student', $student->role);
        $this->assertSame(0, Result::count());

        // 2. Start the assessment.
        $start = $this->actingAs($student)->postJson(route('assessment.sessions.store'));
        $start->assertCreated()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.resumed', false);

        $sessionId = $start->json('data.session_id');
        $this->assertIsInt($sessionId);

        // 3. Read the session: eighteen questions, nothing answered yet.
        $state = $this->actingAs($student)
            ->getJson(route('assessment.show', $sessionId))
            ->assertOk()
            ->assertJsonPath('data.progress.processed', 0)
            ->assertJsonPath('data.progress.total', 18);

        $questions = collect($state->json('data.questions'));
        $this->assertCount(18, $questions);

        // 4. Answer every question, rating the chosen option on some of them.
        foreach ($questions as $index => $question) {
            $primaryOption = collect($question['options'])->first();

            $payload = [
                'primary_option_id' => $primaryOption['option_id'],
                'none_selected' => false,
                'unable_to_judge' => false,
                'ratings' => $index % 3 === 0
                    ? [['option_id' => $primaryOption['option_id'], 'rating' => 2]]
                    : [],
            ];

            $saved = $this->actingAs($student)
                ->putJson(
                    route('assessment.answers.update', [$sessionId, $question['question_id']]),
                    $payload
                )
                ->assertOk()
                ->assertJsonPath('data.saved', true)
                ->assertJsonPath('data.answer.primary_option_id', $primaryOption['option_id']);

            $this->assertSame($index + 1, $saved->json('data.progress.processed'));
        }

        // 5. Complete the assessment.
        $completed = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $sessionId))
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $resultId = $completed->json('data.result_id');
        $this->assertSame(1, Result::count());

        $result = Result::with(['resultScores', 'resultRecommendations'])->firstOrFail();
        $this->assertSame($resultId, $result->id);
        $this->assertSame($sessionId, $result->assessment_session_id);
        $this->assertCount(6, $result->resultScores);
        $this->assertCount(5, $result->resultRecommendations);
    }

    /**
     * Ratings must survive the round trip: what the save response echoes is
     * exactly what the resume payload later reports.
     */
    public function test_saved_ratings_are_visible_when_the_session_is_resumed(): void
    {
        $student = $this->createStudentUser();
        $sessionId = $this->beginSession($student);
        $question = $this->firstQuestion();

        $primary = $question['options'][0];
        $other = $question['options'][1];

        $this->actingAs($student)
            ->putJson(
                route('assessment.answers.update', [$sessionId, $question['question_id']]),
                [
                    'primary_option_id' => $primary['option_id'],
                    'none_selected' => false,
                    'unable_to_judge' => false,
                    'ratings' => [
                        ['option_id' => $primary['option_id'], 'rating' => 2],
                        ['option_id' => $other['option_id'], 'rating' => -1],
                    ],
                ]
            )
            ->assertOk();

        $resumed = $this->actingAs($student)
            ->getJson(route('assessment.show', $sessionId))
            ->assertOk();

        $saved = collect($resumed->json('data.saved_answers'))->firstOrFail();

        $this->assertSame($primary['option_id'], $saved['primary_option_id']);
        $this->assertFalse($saved['none_selected']);
        $this->assertFalse($saved['unable_to_judge']);

        $ratings = collect($saved['ratings'])->keyBy('option_id');
        $this->assertSame(2, $ratings[$primary['option_id']]['rating']);
        $this->assertSame(-1, $ratings[$other['option_id']]['rating']);
    }

    /**
     * The journey is restartable: after finishing, starting again creates a
     * fresh in-progress session and the previous result stays intact.
     */
    public function test_a_student_can_start_a_new_assessment_after_completing_one(): void
    {
        $student = $this->createStudentUser();
        $firstSession = $this->beginSession($student);
        $this->answerAll($student, $firstSession);
        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $firstSession))
            ->assertOk();

        $this->assertSame(1, Result::count());

        $second = $this->actingAs($student)
            ->postJson(route('assessment.sessions.store'))
            ->assertCreated()
            ->assertJsonPath('data.resumed', false);

        $this->assertNotSame($firstSession, $second->json('data.session_id'));
        $this->assertSame(1, Result::count());
        $this->assertSame(2, AssessmentSession::count());
    }

    /**
     * The history endpoint lists the completed result, and the result page
     * renders the persisted scores and recommendations.
     */
    public function test_the_result_appears_in_history_and_can_be_reopened(): void
    {
        $student = $this->createStudentUser();
        $sessionId = $this->beginSession($student);
        $this->answerAll($student, $sessionId);

        $completed = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $sessionId))
            ->assertOk();

        $resultId = $completed->json('data.result_id');

        $history = $this->actingAs($student)
            ->getJson(route('profile.results.index'))
            ->assertOk();

        $this->assertContains($resultId, collect($history->json('data'))->pluck('result_id')->all());

        $this->actingAs($student)
            ->getJson(route('results.show', $resultId))
            ->assertOk()
            ->assertJsonPath('data.result_id', $resultId)
            ->assertJsonCount(6, 'data.scores')
            ->assertJsonCount(5, 'data.recommendations');

        // The HTML page must render too, with the same backend-driven data.
        $this->actingAs($student)
            ->get(route('results.show', $resultId))
            ->assertOk()
            ->assertViewHas('result');
    }

    /**
     * Reopening a result never recomputes it: the persisted rows are the
     * single source of truth across repeated views.
     */
    public function test_reopening_a_result_is_stable_across_views(): void
    {
        $student = $this->createStudentUser();
        $sessionId = $this->beginSession($student);
        $this->answerAll($student, $sessionId);

        $completed = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $sessionId))
            ->assertOk();

        $resultId = $completed->json('data.result_id');

        $first = $this->actingAs($student)
            ->getJson(route('results.show', $resultId))
            ->assertOk()
            ->json('data');

        $second = $this->actingAs($student)
            ->getJson(route('results.show', $resultId))
            ->assertOk()
            ->json('data');

        $this->assertSame($first['scores'], $second['scores']);
        $this->assertSame($first['recommendations'], $second['recommendations']);
        $this->assertSame($first['scoring_version'], $second['scoring_version']);
    }

    /**
     * The completed session becomes read-only: no further answers are accepted.
     */
    public function test_a_completed_session_rejects_further_answers(): void
    {
        $student = $this->createStudentUser();
        $sessionId = $this->beginSession($student);
        $this->answerAll($student, $sessionId);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $sessionId))
            ->assertOk();

        $question = $this->firstQuestion();

        $this->actingAs($student)
            ->putJson(
                route('assessment.answers.update', [$sessionId, $question['question_id']]),
                [
                    'primary_option_id' => $question['options'][0]['option_id'],
                    'none_selected' => false,
                    'unable_to_judge' => false,
                    'ratings' => [],
                ]
            )
            ->assertStatus(409)
            ->assertJsonPath('code', 'SESSION_COMPLETED');
    }

    /**
     * The journey cannot be shortcut: completing a session that has no
     * answers at all is refused and leaves nothing behind.
     */
    public function test_an_unanswered_session_cannot_be_completed(): void
    {
        $student = $this->createStudentUser();
        $sessionId = $this->beginSession($student);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $sessionId))
            ->assertStatus(409)
            ->assertJsonPath('code', 'SESSION_NOT_READY');

        $this->assertSame(0, Result::count());
    }

    private function beginSession(User $student): int
    {
        return $this->actingAs($student)
            ->postJson(route('assessment.sessions.store'))
            ->assertCreated()
            ->json('data.session_id');
    }

    private function answerAll(User $student, int $sessionId): void
    {
        $state = $this->actingAs($student)
            ->getJson(route('assessment.show', $sessionId))
            ->assertOk()
            ->json('data');

        foreach ($state['questions'] as $index => $question) {
            $primary = $question['options'][0];

            $this->actingAs($student)
                ->putJson(
                    route('assessment.answers.update', [$sessionId, $question['question_id']]),
                    [
                        'primary_option_id' => $primary['option_id'],
                        'none_selected' => false,
                        'unable_to_judge' => false,
                        'ratings' => $index % 3 === 0
                            ? [['option_id' => $primary['option_id'], 'rating' => 1]]
                            : [],
                    ]
                )
                ->assertOk();
        }
    }

    /** @return array{question_id: int, options: array<int, array{option_id: int}>} */
    private function firstQuestion(): array
    {
        $question = Question::orderBy('position')->firstOrFail();

        return [
            'question_id' => $question->id,
            'options' => $question->questionOptions
                ->sortBy('position')
                ->map(fn (QuestionOption $option): array => ['option_id' => $option->id])
                ->values()
                ->all(),
        ];
    }
}
