@extends('layouts.app')

@section('title', 'الأساس العلمي لاختبار الميول المهنية')
@section('seo_description', 'تعرّف إلى الأساس العلمي لمسارك: نموذج هولاند RIASEC لقياس الميول المهنية، والأطر المساندة لفهم الثقة والقيم والعوائق واتخاذ قرار تخصص أكثر وعيًا.')

@section('content')
<article class="mx-auto w-full max-w-6xl" dir="rtl">
    <nav aria-label="مسار الصفحة" class="mb-4 text-sm font-semibold text-slate-500">
        <a href="{{ route('home') }}" class="transition-colors hover:text-brand-700">الرئيسية</a>
        <span class="mx-2" aria-hidden="true">/</span>
        <span aria-current="page" class="text-slate-700">الأساس العلمي لمسارك</span>
    </nav>

    <header class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-9 lg:p-12">
        <p class="text-sm font-extrabold text-brand-700">كيف يعمل مسارك علميًا؟</p>
        <h1 class="mt-3 max-w-4xl text-3xl font-extrabold leading-tight text-slate-950 sm:text-4xl lg:text-5xl">
            الأساس العلمي لاختبار الميول المهنية في مسارك
        </h1>
        <p class="mt-5 max-w-4xl text-base leading-8 text-slate-600 sm:text-lg">
            يبني مسارك التقييم على قياس الميول المهنية بوصفها انجذابًا إلى أنشطة وموضوعات وبيئات تعليمية ومهنية، ثم يستخدم أطرًا مساندة لفهم الثقة والخبرة والقيم والعوائق. النتيجة أداة للاستكشاف وليست تشخيصًا للشخصية أو حكمًا نهائيًا على التخصص.
        </p>

        <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <a href="{{ route('career-interests.index') }}" class="btn btn-primary btn-pill min-h-12 px-6 text-base font-extrabold">
                تعرّف إلى اختبار الميول
            </a>
            <a href="{{ route('major-choice.index') }}" class="btn btn-secondary btn-pill min-h-12 px-6 text-base font-bold">
                كيف أختار تخصصي؟
            </a>
        </div>
    </header>

    <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="measure-title">
        <h2 id="measure-title" class="text-2xl font-extrabold text-slate-950">ما الذي يقيسه مسارك فعلًا؟</h2>
        <p class="mt-3 max-w-4xl leading-8 text-slate-600">
            البناء الأساسي في التقييم هو <strong>الميول المهنية</strong>: ما الأنشطة أو الموضوعات أو البيئات التي تميل إلى فعلها أو استكشافها أكثر. لا نفترض أن الميل يساوي القدرة، ولا نخلط رغبة الطالب بما تريده الأسرة أو بما يبدو أعلى مكانة.
        </p>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl bg-slate-50 p-5">
                <h3 class="font-extrabold text-slate-900">نقيس بصورة أساسية</h3>
                <p class="mt-2 leading-7 text-slate-600">تفضيل الأنشطة والموضوعات والبيئات المرتبطة بمجالات الميول المهنية.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-5">
                <h3 class="font-extrabold text-slate-900">ونفصل عنه</h3>
                <p class="mt-2 leading-7 text-slate-600">الخبرة السابقة، والثقة في التعلم، والقيم، والتكلفة المتوقعة، والدعم والعوائق؛ لأنها تساعد على القرار لكنها ليست «درجة ميل» واحدة.</p>
            </div>
        </div>
    </section>

    <section class="mt-6" aria-labelledby="riasec-title">
        <div class="max-w-4xl">
            <p class="text-sm font-extrabold text-brand-700">القياس الأساسي</p>
            <h2 id="riasec-title" class="mt-2 text-2xl font-extrabold text-slate-950">نموذج هولاند RIASEC</h2>
            <p class="mt-3 leading-8 text-slate-600">
                يقسم RIASEC الميول والبيئات المهنية إلى ستة مجالات واسعة. في مسارك يحصل الطالب على درجات منفصلة لهذه المجالات، ولا يُحصر في «نوع شخصية» واحد ثابت.
            </p>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">عملي/تطبيقي</h3>
                <p class="mt-2 leading-7 text-slate-600">الانجذاب إلى الأدوات والمواد والبناء والتنفيذ والعمل الميداني.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">بحثي/تحليلي</h3>
                <p class="mt-2 leading-7 text-slate-600">الانجذاب إلى الاستقصاء والتفسير والتحليل وحل المسائل.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">فني/إبداعي</h3>
                <p class="mt-2 leading-7 text-slate-600">الانجذاب إلى التعبير والتصميم وصنع بدائل بصرية أو مفاهيمية.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">اجتماعي/مساند</h3>
                <p class="mt-2 leading-7 text-slate-600">الانجذاب إلى التعليم والرعاية والإرشاد والتواصل المساعد.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">مبادر/تأثيري</h3>
                <p class="mt-2 leading-7 text-slate-600">الانجذاب إلى القيادة والإقناع والتفاوض وتحريك العمل.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">تنظيمي/إجرائي</h3>
                <p class="mt-2 leading-7 text-slate-600">الانجذاب إلى الدقة والبيانات والسجلات والإجراءات والتنظيم.</p>
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-5 lg:grid-cols-2" aria-labelledby="support-title">
        <div class="lg:col-span-2">
            <h2 id="support-title" class="text-2xl font-extrabold text-slate-950">ما الأطر المساندة للقرار؟</h2>
            <p class="mt-3 max-w-4xl leading-8 text-slate-600">
                لا يحوّل مسارك كل نظرية إلى اختبار مستقل. بعض الأطر تستخدم لفهم النتيجة أو تنظيم الأسئلة التي تساعد الطالب على التفكير بعد ظهور ميوله.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8">
            <h3 class="text-xl font-extrabold text-slate-950">SCCT — الثقة والخبرة والدعم والعوائق</h3>
            <p class="mt-3 leading-8 text-slate-600">
                تساعد النظرية المعرفية الاجتماعية للمسار المهني على التمييز بين ما يميل إليه الطالب وبين ثقته الحالية في قدرته على تعلمه، مع الانتباه إلى الخبرة السابقة والدعم والعوائق. هذه العوامل لا تُدمج تلقائيًا في درجة RIASEC.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8">
            <h3 class="text-xl font-extrabold text-slate-950">SEVT — القيمة والتكلفة والسياق</h3>
            <p class="mt-3 leading-8 text-slate-600">
                يساعد نموذج التوقع والقيمة على التفكير في المتعة والأهمية والمنفعة والتكلفة المتوقعة. يستخدم مسارك هذه الأفكار في المقارنة الواعية، لا لتحويل الظروف المالية أو الاجتماعية إلى «ميل».
            </p>
        </div>
    </section>

    <section class="mt-6 rounded-3xl border border-brand-200 bg-brand-50 p-6 sm:p-8" aria-labelledby="journey-title">
        <h2 id="journey-title" class="text-2xl font-extrabold text-slate-950">كيف تتحول النظريات إلى رحلة عملية؟</h2>
        <p class="mt-3 max-w-4xl leading-8 text-slate-700">
            يستخدم مسارك هيكلًا بسيطًا للقرار: <strong>افهم نفسك</strong> من خلال ميولك وخبرتك وقيمك وقيودك، ثم <strong>افهم الخيارات</strong> من خلال طبيعة الدراسة والمهارات والمسارات، ثم <strong>قارن بوعي</strong> وابحث عن التقارب والتعارض وتحقق بنفسك قبل اتخاذ القرار.
        </p>
        <a href="{{ route('major-choice.index') }}" class="mt-5 inline-flex min-h-11 items-center font-extrabold text-brand-800 hover:text-brand-900">
            اقرأ دليل اختيار التخصص الجامعي
        </a>
    </section>

    <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="limits-title">
        <h2 id="limits-title" class="text-2xl font-extrabold text-slate-950">ما الذي لا يدّعي مسارك قياسه أو ضمانه؟</h2>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <p class="rounded-2xl bg-slate-50 p-4 leading-7 text-slate-600">لا يقيس الذكاء أو القدرة العقلية العامة.</p>
            <p class="rounded-2xl bg-slate-50 p-4 leading-7 text-slate-600">لا يحدد الأهلية الرسمية للقبول الجامعي.</p>
            <p class="rounded-2xl bg-slate-50 p-4 leading-7 text-slate-600">لا يشخّص الصحة النفسية أو الشخصية الشاملة.</p>
            <p class="rounded-2xl bg-slate-50 p-4 leading-7 text-slate-600">لا يضمن النجاح الدراسي أو الوظيفة أو الدخل.</p>
            <p class="rounded-2xl bg-slate-50 p-4 leading-7 text-slate-600">لا يقرر أن هناك «تخصصًا صحيحًا وحيدًا» للطالب.</p>
            <p class="rounded-2xl bg-slate-50 p-4 leading-7 text-slate-600">ولا يحوّل النتيجة إلى حكم نهائي غير قابل للمراجعة.</p>
            <p class="rounded-2xl bg-slate-50 p-4 leading-7 text-slate-600">لا تُقدَّم الدرجات بوصفها مقارنة بمعيار وطني يمني غير متاح للمشروع.</p>
            <p class="rounded-2xl bg-slate-50 p-4 leading-7 text-slate-600 sm:col-span-2 lg:col-span-3">تُفسَّر درجات الميول داخل ملف الطالب نفسه، ولا تُقدَّم بوصفها مقارنة بمعيار وطني يمني غير متاح للمشروع.</p>
        </div>
    </section>

    <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="evidence-title">
        <h2 id="evidence-title" class="text-2xl font-extrabold text-slate-950">المراجع العلمية الأساسية</h2>
        <p class="mt-3 max-w-4xl leading-8 text-slate-600">
            الإطار العلمي الكامل للمشروع يوثّق المراجع وقرارات القياس وحدود الاستخدام. من المراجع الأساسية المستخدمة في بناء الإطار:
        </p>

        <ul class="mt-5 space-y-3 leading-7 text-slate-700">
            <li>
                <a href="https://www.onetcenter.org/reports/IP_Manual.html" target="_blank" rel="noopener noreferrer" class="font-bold text-brand-800 hover:text-brand-900">
                    O*NET Interest Profiler Technical Manual
                </a>
            </li>
            <li>
                <a href="https://doi.org/10.1177/1745691612449021" target="_blank" rel="noopener noreferrer" class="font-bold text-brand-800 hover:text-brand-900">
                    Nye et al. — vocational interests and performance/persistence
                </a>
            </li>
            <li>
                <a href="https://doi.org/10.1016/0001-8791(94)90026-4" target="_blank" rel="noopener noreferrer" class="font-bold text-brand-800 hover:text-brand-900">
                    Lent, Brown & Hackett — Social Cognitive Career Theory
                </a>
            </li>
            <li>
                <a href="https://doi.org/10.1016/j.cedpsych.2020.101859" target="_blank" rel="noopener noreferrer" class="font-bold text-brand-800 hover:text-brand-900">
                    Eccles & Wigfield — Situated Expectancy-Value Theory
                </a>
            </li>
        </ul>
    </section>
</article>
@endsection
