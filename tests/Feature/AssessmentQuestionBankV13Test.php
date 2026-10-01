<?php

namespace Tests\Feature;

use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\User;
use Database\Seeders\AssessmentQuestionBankSeeder;
use Database\Seeders\AssessmentQuestionBankV13Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentQuestionBankV13Test extends TestCase
{
    use RefreshDatabase;

    public function test_v13_is_published_without_mutating_v12(): void
    {
        $this->seed(AssessmentQuestionBankSeeder::class);

        $v12 = AssessmentVersion::where('version_number', 12)->firstOrFail();
        $oldQuestion = $v12->questions()->where('position', 1)->firstOrFail();
        $oldScenario = $oldQuestion->scenario;
        $oldOptionIds = $oldQuestion->questionOptions()->orderBy('position')->pluck('id')->all();

        $student = User::factory()->create(['role' => 'student']);
        $legacySession = AssessmentSession::create([
            'user_id' => $student->id,
            'assessment_version_id' => $v12->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->seed(AssessmentQuestionBankV13Seeder::class);

        $v12->refresh();
        $v13 = AssessmentVersion::where('version_number', 13)->firstOrFail();

        $this->assertSame('retired', $v12->status);
        $this->assertSame('active', $v13->status);
        $this->assertNotNull($v13->published_at);
        $this->assertSame(1, AssessmentVersion::where('status', 'active')->count());

        $this->assertSame($oldScenario, $v12->questions()->where('position', 1)->value('scenario'));
        $this->assertSame(
            $oldOptionIds,
            $v12->questions()->where('position', 1)->firstOrFail()->questionOptions()->orderBy('position')->pluck('id')->all()
        );
        $this->assertDatabaseHas('assessment_sessions', [
            'id' => $legacySession->id,
            'assessment_version_id' => $v12->id,
            'status' => 'in_progress',
        ]);

        $this->assertSame(18, $v13->questions()->count());
        $this->assertSame(
            72,
            $v13->questions()->withCount('questionOptions')->get()->sum('question_options_count')
        );

        $counts = $v13->questions()
            ->join('question_options', 'questions.id', '=', 'question_options.question_id')
            ->selectRaw('question_options.riasec_code, COUNT(*) AS total')
            ->groupBy('question_options.riasec_code')
            ->pluck('total', 'question_options.riasec_code');

        foreach (['R', 'I', 'A', 'S', 'E', 'C'] as $code) {
            $this->assertSame(12, (int) $counts->get($code));
        }

        $this->assertSame(
            'عندكم مناسبة في البيت أو الحارة، والمكان باقي له تجهيز. إيش غالبًا بتسوي؟',
            $v13->questions()->where('position', 1)->value('scenario')
        );

        foreach ($v13->questions()->with('questionOptions')->get() as $question) {
            $this->assertCount(4, $question->questionOptions);
            $this->assertCount(4, $question->questionOptions->pluck('riasec_code')->unique());
        }
    }

    public function test_v13_seeder_is_idempotent_after_success(): void
    {
        $this->seed(AssessmentQuestionBankSeeder::class);
        $this->seed(AssessmentQuestionBankV13Seeder::class);

        $version = AssessmentVersion::where('version_number', 13)->firstOrFail();
        $questionIds = $version->questions()->orderBy('position')->pluck('id')->all();
        $optionIds = $version->questions()->orderBy('position')->get()
            ->flatMap(fn ($question) => $question->questionOptions()->orderBy('position')->pluck('id'))
            ->values()->all();

        $this->seed(AssessmentQuestionBankV13Seeder::class);

        $this->assertSame(1, AssessmentVersion::where('version_number', 13)->count());
        $this->assertSame($questionIds, $version->questions()->orderBy('position')->pluck('id')->all());
        $this->assertSame(
            $optionIds,
            $version->questions()->orderBy('position')->get()
                ->flatMap(fn ($question) => $question->questionOptions()->orderBy('position')->pluck('id'))
                ->values()->all()
        );
        $this->assertSame(1, AssessmentVersion::where('status', 'active')->count());
    }
}
