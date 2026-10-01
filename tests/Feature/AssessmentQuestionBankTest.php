<?php

namespace Tests\Feature;

use App\Models\AssessmentVersion;
use App\Models\Question;
use App\Models\QuestionOption;
use Database\Seeders\AssessmentQuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Verifies that AssessmentQuestionBankSeeder installs the approved v1.2 bank.
 *
 * Expected shape: 1 active version (1.2 stored as 12), 18 questions,
 * 72 options (4 per question), 12 occurrences of each RIASEC code.
 *
 * Scenarios, option texts and codes below are transcribed verbatim from
 * docs/04-assessment/question_bank_specification.md (spec v1.2).
 */
class AssessmentQuestionBankTest extends TestCase
{
    use RefreshDatabase;

    private const VERSION_NUMBER = 12;

    private const APPROVED_CODES = ['R', 'I', 'A', 'S', 'E', 'C'];

    private const FIFTH_OPTION_TEXT = 'لا يشبهني أي من هذه التصرفات';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AssessmentQuestionBankSeeder::class);
    }

    public function test_assessment_version_1_2_is_created_as_active(): void
    {
        $version = AssessmentVersion::where('version_number', self::VERSION_NUMBER)->first();

        $this->assertNotNull($version, 'Assessment version 1.2 (stored as 12) was not created.');
        $this->assertSame('active', $version->status);
        $this->assertNotNull($version->published_at);
    }

    public function test_seeds_exactly_eighteen_questions(): void
    {
        $this->assertSame(18, Question::count(), 'The bank must contain exactly 18 questions.');
    }

    public function test_seeds_exactly_seventy_two_options(): void
    {
        $this->assertSame(72, QuestionOption::count(), 'The bank must contain exactly 72 options.');
    }

    public function test_every_question_has_exactly_four_options(): void
    {
        $questions = Question::with('questionOptions')->orderBy('position')->get();

        $questions->each(function (Question $question): void {
            $this->assertCount(
                4,
                $question->questionOptions,
                "Question at position {$question->position} must have exactly 4 options."
            );
        });
    }

    public function test_riasec_codes_are_within_the_approved_set(): void
    {
        $codes = QuestionOption::pluck('riasec_code')->unique()->all();

        sort($codes);
        $expected = self::APPROVED_CODES;
        sort($expected);

        $this->assertSame($expected, $codes, 'Only R, I, A, S, E, C codes are allowed.');
    }

    public function test_riasec_distribution_is_balanced_at_twelve_each(): void
    {
        $counts = QuestionOption::query()
            ->selectRaw('riasec_code, COUNT(*) as total')
            ->groupBy('riasec_code')
            ->pluck('total', 'riasec_code');

        foreach (self::APPROVED_CODES as $code) {
            $this->assertSame(
                12,
                (int) $counts->get($code, 0),
                "RIASEC code {$code} must appear exactly 12 times."
            );
        }
    }

    public function test_question_positions_are_not_duplicated_within_the_version(): void
    {
        $positions = Question::orderBy('position')->pluck('position')->all();

        $this->assertSame($positions, array_unique($positions), 'Question positions must not repeat.');
        $this->assertSame(range(1, 18), $positions, 'Question positions must be a contiguous 1..18 range.');
    }

    public function test_option_positions_are_not_duplicated_within_each_question(): void
    {
        Question::with('questionOptions')->orderBy('position')->get()
            ->each(function (Question $question): void {
                $positions = $question->questionOptions->pluck('position')->all();

                $this->assertSame(
                    $positions,
                    array_unique($positions),
                    "Option positions must not repeat in question {$question->position}."
                );
                $this->assertSame(
                    range(1, 4),
                    $positions,
                    "Option positions must be a contiguous 1..4 range in question {$question->position}."
                );
            });
    }

    public function test_no_fifth_option_is_stored_in_question_options(): void
    {
        $exists = QuestionOption::query()
            ->where('option_text', 'like', '%'.self::FIFTH_OPTION_TEXT.'%')
            ->exists();

        $this->assertFalse(
            $exists,
            'The fifth option must not be stored as a question_option row.'
        );

        $this->assertSame(0, AssessmentVersion::first()->questions()->get()->flatMap->questionOptions->count() % 4);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(AssessmentQuestionBankSeeder::class);
        $this->seed(AssessmentQuestionBankSeeder::class);

        $this->assertSame(1, AssessmentVersion::where('version_number', self::VERSION_NUMBER)->count());
        $this->assertSame(18, Question::count());
        $this->assertSame(72, QuestionOption::count());
    }

    /**
     * Full bank, verbatim from the spec. Each entry is a positional argument
     * list: [$position, $scenario, $options] where $options is a list of
     * ['code' => ..., 'text' => ...] in presentation order.
     */
    public static function bankProvider(): array
    {
        return [
            'Q01' => [
                1,
                'عندكم مناسبة في البيت أو الحارة، والمكان باقي له تجهيز. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'R', 'text' => 'أبدأ أجهز المكان بنفسي وأخلص اللي يحتاج شغل.'],
                    ['code' => 'S', 'text' => 'أجيب واحد من أصحابي ونشتغل سوا.'],
                    ['code' => 'E', 'text' => 'أوزع الشغل وأقول لكل واحد إيش يعمل.'],
                    ['code' => 'I', 'text' => 'أشوف أول إيش ناقص وإيش الأفضل، وبعدها أقرر من وين نبدأ.'],
                ],
            ],

            'Q02' => [
                2,
                'عندكم مكان في البيت للكتب والأغراض، لكن بعض الأشياء تضيع أو تتلف. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'R', 'text' => 'أفحص الكتب والأغراض بنفسي وأشوف إذا في شيء ناقص أو تالف.'],
                    ['code' => 'C', 'text' => 'أعيد ترتيب الكتب والأغراض بطريقة واضحة وأسهل للاستخدام.'],
                    ['code' => 'I', 'text' => 'أشوف ليش الأشياء تضيع أو تتلف وأحاول أعرف السبب.'],
                    ['code' => 'A', 'text' => 'أفكر بطريقة جديدة نخزن فيها الأشياء وتكون أسهل وأوضح.'],
                ],
            ],

            'Q03' => [
                3,
                'تأخر الماء عن البيت أو الحارة، والماء الموجود قرب يخلص. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'R', 'text' => 'أستخدم الموجود، وإذا خلص أروح أجيب ماء من مكان ثاني لين يرجع الماء.'],
                    ['code' => 'C', 'text' => 'أشوف كم باقي معنا ونقتصد فيه لين يرجع الماء.'],
                    ['code' => 'E', 'text' => 'أتواصل مع المسؤولين وأسألهم متى بيرجع الماء.'],
                    ['code' => 'I', 'text' => 'أشوف ليش الماء اتأخر وأدور على حل يمنع أو يخفف المشكلة.'],
                ],
            ],

            'Q04' => [
                4,
                'طفل أصغر منك سألك عن شيء ما فهمه أو كيف يشتغل شيء يشوفه كل يوم. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'C', 'text' => 'أشرح له ببساطة خطوة خطوة لين يفهم.'],
                    ['code' => 'R', 'text' => 'أوريه الشيء بنفسه أو أخليه يجربه قدامي.'],
                    ['code' => 'A', 'text' => 'أعطيه مثال أو قصة بسيطة تخليه يفهم الفكرة.'],
                    ['code' => 'S', 'text' => 'أشجعه يسأل أكثر وأخليه يحاول يوصل للإجابة بنفسه.'],
                ],
            ],

            'Q05' => [
                5,
                'في الحارة أو بين أصحابك في مشكلة تتكرر، زي رمي الزبالة في مكان غلط أو أي تصرف يزعج الناس. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'I', 'text' => 'أشوف وين المشكلة بالضبط وإيش أكثر شيء مسببها.'],
                    ['code' => 'A', 'text' => 'أحط إشارات أو لوحات بسيطة تنبه الناس للمشكلة.'],
                    ['code' => 'E', 'text' => 'أتكلم مع العقال أو المسؤولين عن الموضوع وأطلب منهم يتدخلوا.'],
                    ['code' => 'S', 'text' => 'أتكلم مع الناس اللي يتضرروا من المشكلة ونشوف كيف نساعد بعض.'],
                ],
            ],

            'Q06' => [
                6,
                'خلص شرح الدرس، وفي نقطة ما فهمتها كويس. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'R', 'text' => 'أجرب عليها أمثلة لين أفهمها.'],
                    ['code' => 'S', 'text' => 'أسأل المدرس يعيدها لي، أو أخلي واحد من زملائي يشرحها.'],
                    ['code' => 'I', 'text' => 'أبحث عن الموضوع وأحاول أفهمه بنفسي.'],
                    ['code' => 'A', 'text' => 'أخترع لها مثال من حياتي عشان توضح لي.'],
                ],
            ],

            'Q07' => [
                7,
                'اتفقت مع كم واحد من زملائك تراجعوا سوا، لكن الوقت بدأ يضيع وما أنجزتوا شي واضح. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'I', 'text' => 'أشوف ليش الوقت يضيع وأقترح طريقة نمشي عليها.'],
                    ['code' => 'S', 'text' => 'أشوف الجديين من الزملاء وأراجع معهم عشان ما نضيع الوقت.'],
                    ['code' => 'E', 'text' => 'أحرك الشباب وأقول يلا نبدأ ونركز في المراجعة.'],
                    ['code' => 'C', 'text' => 'أقسم الوقت والمواضيع وأتابع إيش خلصنا وإيش باقي.'],
                ],
            ],

            'Q08' => [
                8,
                'قربت الاختبارات وبدأت تجهز نفسك للمذاكرة. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'A', 'text' => 'أجهز لي ملخص أو طريقة خاصة أراجع بها المواد.'],
                    ['code' => 'I', 'text' => 'أراجع المواد وأشوف إيش الأصعب عليّ وأعطيه وقت أكثر.'],
                    ['code' => 'S', 'text' => 'أذاكر مع كم واحد من زملائي ونراجع سوا.'],
                    ['code' => 'C', 'text' => 'أعمل لي جدول مذاكرة وأحدد متى أذاكر كل مادة.'],
                ],
            ],

            'Q09' => [
                9,
                'طلعت نتيجتك في مادة أقل من اللي كنت متوقعه. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'I', 'text' => 'أرجع أشوف أخطائي وأعرف وين كانت المشكلة.'],
                    ['code' => 'A', 'text' => 'أخترع لي طريقة ثانية أفهم بها الدرس.'],
                    ['code' => 'S', 'text' => 'أسأل المدرس أو واحد شاطر في المادة يساعدني في اللي ما فهمته.'],
                    ['code' => 'C', 'text' => 'أحدد الدروس اللي أنا ضعيف فيها وأرتب لها وقت أراجعها.'],
                ],
            ],

            'Q10' => [
                10,
                'قبل ما تبدأ الحصة، الفصل محتاج ترتيب وتجهيز. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'R', 'text' => 'أبدأ أنقل المقاعد والكتب وأرتبها بنفسي.'],
                    ['code' => 'A', 'text' => 'أفكر بترتيب يخلي الفصل أريح وأسهل للاستخدام.'],
                    ['code' => 'S', 'text' => 'أشوف مين يحتاج مساعدة وأشتغل معه.'],
                    ['code' => 'E', 'text' => 'أقسم الشغل بين الزملاء وأقول لكل واحد إيش يعمل.'],
                ],
            ],

            'Q11' => [
                11,
                'عندك وقت فراغ وما عندك شيء تسويه. إيش غالبًا تختار؟',
                [
                    ['code' => 'R', 'text' => 'أشتغل على شيء بيدي، أصلحه أو أركبه.'],
                    ['code' => 'A', 'text' => 'أكتب أو أصمم أو أسوي شيء من خيالي.'],
                    ['code' => 'E', 'text' => 'أتواصل مع أصحابي وأرتب نخرج نتمشى سوا.'],
                    ['code' => 'C', 'text' => 'أرتب أغراضي أو المكان اللي أجلس فيه.'],
                ],
            ],

            'Q12' => [
                12,
                'بتشتري شيء تحتاجه، وقدامك أكثر من خيار. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'R', 'text' => 'أشوفه بنفسي وأتأكد إنه كويس وينفع لي.'],
                    ['code' => 'I', 'text' => 'أبحث عنه وأقارن بين الأسعار والجودة قبل ما أشتري.'],
                    ['code' => 'E', 'text' => 'أكلم البائع وأحاول أوصل معه لسعر يناسبني.'],
                    ['code' => 'C', 'text' => 'أحدد كم معي وإيش المواصفات اللي أحتاجها قبل ما أشتري.'],
                ],
            ],

            'Q13' => [
                13,
                'اختلف اثنين من أصحابك والخلاف بينهم كبر. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'I', 'text' => 'أسأل كل واحد إيش حصل وأحاول أعرف سبب الخلاف.'],
                    ['code' => 'S', 'text' => 'أجلس مع اللي متضايق منهم وأحاول أهديه وأوقف معه.'],
                    ['code' => 'E', 'text' => 'أجمع الاثنين وأحاول أخليهم يتفاهموا وينهوا الخلاف.'],
                    ['code' => 'R', 'text' => 'إذا بدأ الموضوع يكبر، أتدخل وأفصل بينهم قبل ما يزيد.'],
                ],
            ],

            'Q14' => [
                14,
                'في مكان فاضي قريب منكم وممكن تستفيدوا منه. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'R', 'text' => 'أبدأ أنظفه وأجهزه بيدي.'],
                    ['code' => 'A', 'text' => 'أفكر بفكرة مختلفة للمكان تخليه أحسن وأجمل.'],
                    ['code' => 'S', 'text' => 'أسأل الناس إيش أكثر شيء يحتاجوه في المكان.'],
                    ['code' => 'E', 'text' => 'أجمع الشباب وأرتب معهم كيف نحوله لشيء مفيد.'],
                ],
            ],

            'Q15' => [
                15,
                'عندكم فعالية في المدرسة، وكل واحد بيختار الشيء اللي يحب يشارك فيه. إيش غالبًا تختار؟',
                [
                    ['code' => 'A', 'text' => 'أجهز شيء أشارك به في الفعالية، مثل لوحة أو فكرة من عندي.'],
                    ['code' => 'S', 'text' => 'أكون مع الطلاب وأساعدهم في اللي يحتاجوه.'],
                    ['code' => 'E', 'text' => 'أكون المتحدث أو المقدم قدام الحاضرين في الفعالية.'],
                    ['code' => 'C', 'text' => 'أرتب الوقت والمهام وأتأكد إن كل شيء ماشي بالترتيب.'],
                ],
            ],

            'Q16' => [
                16,
                'جوالك أو جهازك بدأ يشتغل بشكل غريب ويعلق. إيش أقرب تصرف تسويه؟',
                [
                    ['code' => 'C', 'text' => 'أرتب آخذه عند مهندس معروف يصلحه بدل ما أجرب فيه.'],
                    ['code' => 'R', 'text' => 'أجرب حلول عملية وأحاول أصلحه بنفسي.'],
                    ['code' => 'A', 'text' => 'أدور على طريقة مختلفة أو حل جديد أجربه.'],
                    ['code' => 'I', 'text' => 'ألاحظ متى يعلق الجهاز وإيش كنت أسوي وقتها، وأحاول أعرف السبب.'],
                ],
            ],

            'Q17' => [
                17,
                'يوجد عطل في البيت، مثل تسرب ماء أو مشكلة في الكهرباء. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'R', 'text' => 'أحاول أصلح المشكلة بنفسي.'],
                    ['code' => 'E', 'text' => 'أتصل بشخص فاهم وأرتب معه يجي يصلح المشكلة.'],
                    ['code' => 'A', 'text' => 'أفكر بحل مؤقت آمن يمنع المشكلة تكبر لين تنصلح.'],
                    ['code' => 'C', 'text' => 'أوقف استخدام الشيء المتعطل وأرتب الوضع مؤقتًا لين تنصلح المشكلة.'],
                ],
            ],

            'Q18' => [
                18,
                'سمعت معلومة منتشرة بين الناس، وبعدها عرفت إنها مش صحيحة. إيش غالبًا بتسوي؟',
                [
                    ['code' => 'S', 'text' => 'أرسل لهم المصدر اللي تأكدت منه وأقول لهم شوفوا المعلومة الصحيحة.'],
                    ['code' => 'E', 'text' => 'أحاول أقنع الناس إن هذه مجرد معلومة شائعة ومش صحيحة.'],
                    ['code' => 'I', 'text' => 'أشوف إيش السبب اللي خلى المعلومة تنتشر بين الناس.'],
                    ['code' => 'C', 'text' => 'أتأكد من المصدر الصحيح قبل ما أتكلم عنها أو أنقلها لغيري.'],
                ],
            ],

        ];
    }

    #[DataProvider('bankProvider')]
    public function test_seeded_bank_matches_the_specification_verbatim(int $position, string $scenario, array $options): void
    {
        $question = Question::where('position', $position)->first();

        $this->assertNotNull($question, "Question at position {$position} is missing.");
        $this->assertSame($scenario, $question->scenario, "Scenario at position {$position} does not match the spec.");

        $seeded = $question->questionOptions()
            ->orderBy('position')
            ->get()
            ->map(fn (QuestionOption $option): array => [
                'code' => $option->riasec_code,
                'text' => $option->option_text,
            ])
            ->all();

        $this->assertSame($options, $seeded, "Options at position {$position} do not match the spec.");
    }
}
