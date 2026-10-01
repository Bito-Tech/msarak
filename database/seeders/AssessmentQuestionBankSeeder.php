<?php

namespace Database\Seeders;

use App\Models\AssessmentVersion;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the approved v1.2 assessment question bank.
 *
 * Source of truth: docs/04-assessment/question_bank_specification.md
 * (18 scenarios x 4 options = 72 options, 12 occurrences per RIASEC code).
 *
 * NOTE: the `version_number` column is an unsigned integer, so the semantic
 * version "1.2" is stored as 12.
 */
class AssessmentQuestionBankSeeder extends Seeder
{
    /**
     * Expected counts, used for the built-in self-validation.
     */
    private const QUESTIONS_COUNT = 18;

    private const OPTIONS_PER_QUESTION = 4;

    private const RIASEC_CODES = ['R', 'I', 'A', 'S', 'E', 'C'];

    private const OPTIONS_PER_CODE = 12;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questionsData = $this->questionsData();

        DB::transaction(function () use ($questionsData): void {
            // Semantic version "1.2" is stored as the integer 12.
            $version = AssessmentVersion::updateOrCreate(
                ['version_number' => 12],
                [
                    'status' => 'active',
                    'published_at' => now(),
                ]
            );

            foreach ($questionsData as $position => $questionData) {
                $question = Question::updateOrCreate(
                    [
                        'assessment_version_id' => $version->id,
                        'position' => $position,
                    ],
                    [
                        'scenario' => $questionData['scenario'],
                    ]
                );

                foreach ($questionData['options'] as $optionPosition => $optionData) {
                    QuestionOption::updateOrCreate(
                        [
                            'question_id' => $question->id,
                            'position' => $optionPosition,
                        ],
                        [
                            'option_text' => $optionData['text'],
                            'riasec_code' => $optionData['code'],
                        ]
                    );
                }
            }

            $this->validate($version);
        });
    }

    /**
     * Verify the seeded bank matches the approved specification.
     *
     * Runs inside the transaction, so a failed check rolls everything back.
     *
     * @throws \RuntimeException
     */
    private function validate(AssessmentVersion $version): void
    {
        $questions = $version->questions()
            ->orderBy('position')
            ->get();

        if ($questions->count() !== self::QUESTIONS_COUNT) {
            throw new \RuntimeException(sprintf(
                'Expected %d questions, got %d.',
                self::QUESTIONS_COUNT,
                $questions->count()
            ));
        }

        // Detect duplicated question positions within this version.
        $questionPositions = $questions->pluck('position')->all();
        if (count($questionPositions) !== count(array_unique($questionPositions))) {
            throw new \RuntimeException('Duplicate question positions found within the version.');
        }

        $totalOptions = 0;
        $codeCounts = array_fill_keys(self::RIASEC_CODES, 0);

        foreach ($questions as $question) {
            $options = $question->questionOptions()
                ->orderBy('position')
                ->get();

            if ($options->count() !== self::OPTIONS_PER_QUESTION) {
                throw new \RuntimeException(sprintf(
                    'Question at position %d: expected %d options, got %d.',
                    $question->position,
                    self::OPTIONS_PER_QUESTION,
                    $options->count()
                ));
            }

            // Detect duplicated option positions within a question.
            $optionPositions = $options->pluck('position')->all();
            if (count($optionPositions) !== count(array_unique($optionPositions))) {
                throw new \RuntimeException(sprintf(
                    'Duplicate option positions in question at position %d.',
                    $question->position
                ));
            }

            foreach ($options as $option) {
                $codeCounts[$option->riasec_code]++;
                $totalOptions++;
            }
        }

        if ($totalOptions !== self::QUESTIONS_COUNT * self::OPTIONS_PER_QUESTION) {
            throw new \RuntimeException(sprintf(
                'Expected %d options, got %d.',
                self::QUESTIONS_COUNT * self::OPTIONS_PER_QUESTION,
                $totalOptions
            ));
        }

        foreach ($codeCounts as $code => $count) {
            if ($count !== self::OPTIONS_PER_CODE) {
                throw new \RuntimeException(sprintf(
                    'RIASEC code %s: expected %d occurrences, got %d.',
                    $code,
                    self::OPTIONS_PER_CODE,
                    $count
                ));
            }
        }
    }

    /**
     * The approved question bank, transcribed verbatim from
     * docs/04-assessment/question_bank_specification.md (spec v1.2).
     *
     * Option order follows the specification. Presentation-time shuffling is a
     * runtime concern and is intentionally NOT applied here.
     *
     * @return array<int, array{scenario: string, options: array<int, array{code: string, text: string}>}>
     */
    private function questionsData(): array
    {
        return [
            1 => [
                'scenario' => 'عندكم مناسبة في البيت أو الحارة، والمكان باقي له تجهيز. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أبدأ أجهز المكان بنفسي وأخلص اللي يحتاج شغل.'],
                    2 => ['code' => 'S', 'text' => 'أجيب واحد من أصحابي ونشتغل سوا.'],
                    3 => ['code' => 'E', 'text' => 'أوزع الشغل وأقول لكل واحد إيش يعمل.'],
                    4 => ['code' => 'I', 'text' => 'أشوف أول إيش ناقص وإيش الأفضل، وبعدها أقرر من وين نبدأ.'],
                ],
            ],

            2 => [
                'scenario' => 'عندكم مكان في البيت للكتب والأغراض، لكن بعض الأشياء تضيع أو تتلف. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أفحص الكتب والأغراض بنفسي وأشوف إذا في شيء ناقص أو تالف.'],
                    2 => ['code' => 'C', 'text' => 'أعيد ترتيب الكتب والأغراض بطريقة واضحة وأسهل للاستخدام.'],
                    3 => ['code' => 'I', 'text' => 'أشوف ليش الأشياء تضيع أو تتلف وأحاول أعرف السبب.'],
                    4 => ['code' => 'A', 'text' => 'أفكر بطريقة جديدة نخزن فيها الأشياء وتكون أسهل وأوضح.'],
                ],
            ],

            3 => [
                'scenario' => 'تأخر الماء عن البيت أو الحارة، والماء الموجود قرب يخلص. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أستخدم الموجود، وإذا خلص أروح أجيب ماء من مكان ثاني لين يرجع الماء.'],
                    2 => ['code' => 'C', 'text' => 'أشوف كم باقي معنا ونقتصد فيه لين يرجع الماء.'],
                    3 => ['code' => 'E', 'text' => 'أتواصل مع المسؤولين وأسألهم متى بيرجع الماء.'],
                    4 => ['code' => 'I', 'text' => 'أشوف ليش الماء اتأخر وأدور على حل يمنع أو يخفف المشكلة.'],
                ],
            ],

            4 => [
                'scenario' => 'طفل أصغر منك سألك عن شيء ما فهمه أو كيف يشتغل شيء يشوفه كل يوم. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'C', 'text' => 'أشرح له ببساطة خطوة خطوة لين يفهم.'],
                    2 => ['code' => 'R', 'text' => 'أوريه الشيء بنفسه أو أخليه يجربه قدامي.'],
                    3 => ['code' => 'A', 'text' => 'أعطيه مثال أو قصة بسيطة تخليه يفهم الفكرة.'],
                    4 => ['code' => 'S', 'text' => 'أشجعه يسأل أكثر وأخليه يحاول يوصل للإجابة بنفسه.'],
                ],
            ],

            5 => [
                'scenario' => 'في الحارة أو بين أصحابك في مشكلة تتكرر، زي رمي الزبالة في مكان غلط أو أي تصرف يزعج الناس. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'I', 'text' => 'أشوف وين المشكلة بالضبط وإيش أكثر شيء مسببها.'],
                    2 => ['code' => 'A', 'text' => 'أحط إشارات أو لوحات بسيطة تنبه الناس للمشكلة.'],
                    3 => ['code' => 'E', 'text' => 'أتكلم مع العقال أو المسؤولين عن الموضوع وأطلب منهم يتدخلوا.'],
                    4 => ['code' => 'S', 'text' => 'أتكلم مع الناس اللي يتضرروا من المشكلة ونشوف كيف نساعد بعض.'],
                ],
            ],

            6 => [
                'scenario' => 'خلص شرح الدرس، وفي نقطة ما فهمتها كويس. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أجرب عليها أمثلة لين أفهمها.'],
                    2 => ['code' => 'S', 'text' => 'أسأل المدرس يعيدها لي، أو أخلي واحد من زملائي يشرحها.'],
                    3 => ['code' => 'I', 'text' => 'أبحث عن الموضوع وأحاول أفهمه بنفسي.'],
                    4 => ['code' => 'A', 'text' => 'أخترع لها مثال من حياتي عشان توضح لي.'],
                ],
            ],

            7 => [
                'scenario' => 'اتفقت مع كم واحد من زملائك تراجعوا سوا، لكن الوقت بدأ يضيع وما أنجزتوا شي واضح. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'I', 'text' => 'أشوف ليش الوقت يضيع وأقترح طريقة نمشي عليها.'],
                    2 => ['code' => 'S', 'text' => 'أشوف الجديين من الزملاء وأراجع معهم عشان ما نضيع الوقت.'],
                    3 => ['code' => 'E', 'text' => 'أحرك الشباب وأقول يلا نبدأ ونركز في المراجعة.'],
                    4 => ['code' => 'C', 'text' => 'أقسم الوقت والمواضيع وأتابع إيش خلصنا وإيش باقي.'],
                ],
            ],

            8 => [
                'scenario' => 'قربت الاختبارات وبدأت تجهز نفسك للمذاكرة. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'A', 'text' => 'أجهز لي ملخص أو طريقة خاصة أراجع بها المواد.'],
                    2 => ['code' => 'I', 'text' => 'أراجع المواد وأشوف إيش الأصعب عليّ وأعطيه وقت أكثر.'],
                    3 => ['code' => 'S', 'text' => 'أذاكر مع كم واحد من زملائي ونراجع سوا.'],
                    4 => ['code' => 'C', 'text' => 'أعمل لي جدول مذاكرة وأحدد متى أذاكر كل مادة.'],
                ],
            ],

            9 => [
                'scenario' => 'طلعت نتيجتك في مادة أقل من اللي كنت متوقعه. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'I', 'text' => 'أرجع أشوف أخطائي وأعرف وين كانت المشكلة.'],
                    2 => ['code' => 'A', 'text' => 'أخترع لي طريقة ثانية أفهم بها الدرس.'],
                    3 => ['code' => 'S', 'text' => 'أسأل المدرس أو واحد شاطر في المادة يساعدني في اللي ما فهمته.'],
                    4 => ['code' => 'C', 'text' => 'أحدد الدروس اللي أنا ضعيف فيها وأرتب لها وقت أراجعها.'],
                ],
            ],

            10 => [
                'scenario' => 'قبل ما تبدأ الحصة، الفصل محتاج ترتيب وتجهيز. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أبدأ أنقل المقاعد والكتب وأرتبها بنفسي.'],
                    2 => ['code' => 'A', 'text' => 'أفكر بترتيب يخلي الفصل أريح وأسهل للاستخدام.'],
                    3 => ['code' => 'S', 'text' => 'أشوف مين يحتاج مساعدة وأشتغل معه.'],
                    4 => ['code' => 'E', 'text' => 'أقسم الشغل بين الزملاء وأقول لكل واحد إيش يعمل.'],
                ],
            ],

            11 => [
                'scenario' => 'عندك وقت فراغ وما عندك شيء تسويه. إيش غالبًا تختار؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أشتغل على شيء بيدي، أصلحه أو أركبه.'],
                    2 => ['code' => 'A', 'text' => 'أكتب أو أصمم أو أسوي شيء من خيالي.'],
                    3 => ['code' => 'E', 'text' => 'أتواصل مع أصحابي وأرتب نخرج نتمشى سوا.'],
                    4 => ['code' => 'C', 'text' => 'أرتب أغراضي أو المكان اللي أجلس فيه.'],
                ],
            ],

            12 => [
                'scenario' => 'بتشتري شيء تحتاجه، وقدامك أكثر من خيار. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أشوفه بنفسي وأتأكد إنه كويس وينفع لي.'],
                    2 => ['code' => 'I', 'text' => 'أبحث عنه وأقارن بين الأسعار والجودة قبل ما أشتري.'],
                    3 => ['code' => 'E', 'text' => 'أكلم البائع وأحاول أوصل معه لسعر يناسبني.'],
                    4 => ['code' => 'C', 'text' => 'أحدد كم معي وإيش المواصفات اللي أحتاجها قبل ما أشتري.'],
                ],
            ],

            13 => [
                'scenario' => 'اختلف اثنين من أصحابك والخلاف بينهم كبر. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'I', 'text' => 'أسأل كل واحد إيش حصل وأحاول أعرف سبب الخلاف.'],
                    2 => ['code' => 'S', 'text' => 'أجلس مع اللي متضايق منهم وأحاول أهديه وأوقف معه.'],
                    3 => ['code' => 'E', 'text' => 'أجمع الاثنين وأحاول أخليهم يتفاهموا وينهوا الخلاف.'],
                    4 => ['code' => 'R', 'text' => 'إذا بدأ الموضوع يكبر، أتدخل وأفصل بينهم قبل ما يزيد.'],
                ],
            ],

            14 => [
                'scenario' => 'في مكان فاضي قريب منكم وممكن تستفيدوا منه. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أبدأ أنظفه وأجهزه بيدي.'],
                    2 => ['code' => 'A', 'text' => 'أفكر بفكرة مختلفة للمكان تخليه أحسن وأجمل.'],
                    3 => ['code' => 'S', 'text' => 'أسأل الناس إيش أكثر شيء يحتاجوه في المكان.'],
                    4 => ['code' => 'E', 'text' => 'أجمع الشباب وأرتب معهم كيف نحوله لشيء مفيد.'],
                ],
            ],

            15 => [
                'scenario' => 'عندكم فعالية في المدرسة، وكل واحد بيختار الشيء اللي يحب يشارك فيه. إيش غالبًا تختار؟',
                'options' => [
                    1 => ['code' => 'A', 'text' => 'أجهز شيء أشارك به في الفعالية، مثل لوحة أو فكرة من عندي.'],
                    2 => ['code' => 'S', 'text' => 'أكون مع الطلاب وأساعدهم في اللي يحتاجوه.'],
                    3 => ['code' => 'E', 'text' => 'أكون المتحدث أو المقدم قدام الحاضرين في الفعالية.'],
                    4 => ['code' => 'C', 'text' => 'أرتب الوقت والمهام وأتأكد إن كل شيء ماشي بالترتيب.'],
                ],
            ],

            16 => [
                'scenario' => 'جوالك أو جهازك بدأ يشتغل بشكل غريب ويعلق. إيش أقرب تصرف تسويه؟',
                'options' => [
                    1 => ['code' => 'C', 'text' => 'أرتب آخذه عند مهندس معروف يصلحه بدل ما أجرب فيه.'],
                    2 => ['code' => 'R', 'text' => 'أجرب حلول عملية وأحاول أصلحه بنفسي.'],
                    3 => ['code' => 'A', 'text' => 'أدور على طريقة مختلفة أو حل جديد أجربه.'],
                    4 => ['code' => 'I', 'text' => 'ألاحظ متى يعلق الجهاز وإيش كنت أسوي وقتها، وأحاول أعرف السبب.'],
                ],
            ],

            17 => [
                'scenario' => 'يوجد عطل في البيت، مثل تسرب ماء أو مشكلة في الكهرباء. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'R', 'text' => 'أحاول أصلح المشكلة بنفسي.'],
                    2 => ['code' => 'E', 'text' => 'أتصل بشخص فاهم وأرتب معه يجي يصلح المشكلة.'],
                    3 => ['code' => 'A', 'text' => 'أفكر بحل مؤقت آمن يمنع المشكلة تكبر لين تنصلح.'],
                    4 => ['code' => 'C', 'text' => 'أوقف استخدام الشيء المتعطل وأرتب الوضع مؤقتًا لين تنصلح المشكلة.'],
                ],
            ],

            18 => [
                'scenario' => 'سمعت معلومة منتشرة بين الناس، وبعدها عرفت إنها مش صحيحة. إيش غالبًا بتسوي؟',
                'options' => [
                    1 => ['code' => 'S', 'text' => 'أرسل لهم المصدر اللي تأكدت منه وأقول لهم شوفوا المعلومة الصحيحة.'],
                    2 => ['code' => 'E', 'text' => 'أحاول أقنع الناس إن هذه مجرد معلومة شائعة ومش صحيحة.'],
                    3 => ['code' => 'I', 'text' => 'أشوف إيش السبب اللي خلى المعلومة تنتشر بين الناس.'],
                    4 => ['code' => 'C', 'text' => 'أتأكد من المصدر الصحيح قبل ما أتكلم عنها أو أنقلها لغيري.'],
                ],
            ],

        ];
    }
}
