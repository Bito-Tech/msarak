@extends('layouts.app')

@section('title', 'كيف أختار تخصصي الجامعي؟ | دليل عملي للطلاب')
@section('seo_description', 'كيف تختار تخصصك الجامعي؟ ابدأ بفهم ميولك وقدراتك وقيمك، ثم افهم طبيعة التخصص وقارن الخيارات بخطوات عملية تساعدك على اتخاذ قرار واعٍ بعد الثانوية.')

@section('content')
<article class="mx-auto w-full max-w-6xl" dir="rtl">
    <nav aria-label="مسار الصفحة" class="mb-4 text-sm font-semibold text-slate-500">
        <a href="{{ route('home') }}" class="transition-colors hover:text-brand-700">الرئيسية</a>
        <span class="mx-2" aria-hidden="true">/</span>
        <span aria-current="page" class="text-slate-700">كيف أختار تخصصي الجامعي؟</span>
    </nav>

    <header class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
        <div class="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="flex flex-col justify-center p-6 sm:p-9 lg:p-12">
                <p class="text-sm font-extrabold text-brand-700">دليل عملي بعد الثانوية</p>
                <h1 class="mt-3 text-3xl font-extrabold leading-tight text-slate-950 sm:text-4xl lg:text-5xl">
                    كيف أختار تخصصي الجامعي؟
                </h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600 sm:text-lg">
                    لا يبدأ الاختيار باسم التخصص، بل بفهم نفسك أولًا، ثم فهم الخيارات المتاحة، ثم المقارنة بينها بوعي. الهدف ليس العثور على «تخصص مثالي»، بل الوصول إلى قرار تستطيع تفسيره والتحقق منه.
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ route('career-interests.index') }}" class="btn btn-primary btn-pill min-h-12 px-6 text-base font-extrabold">
                        ابدأ بفهم ميولك
                    </a>
                    <a href="{{ route('specializations.index') }}" class="btn btn-secondary btn-pill min-h-12 px-6 text-base font-bold">
                        استكشف التخصصات
                    </a>
                </div>
            </div>

            <div class="relative min-h-72 overflow-hidden bg-brand-50 lg:min-h-[28rem]">
                <img
                    src="/assets/home/masarak-home-student-hero.jpg"
                    alt="طالب يفكر في اختيار تخصصه الجامعي بعد الثانوية"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="eager"
                    fetchpriority="high">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/20 via-transparent to-transparent" aria-hidden="true"></div>
            </div>
        </div>
    </header>

    <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="three-steps-title">
        <p class="text-sm font-extrabold text-brand-700">ثلاث خطوات أساسية</p>
        <h2 id="three-steps-title" class="mt-2 text-2xl font-extrabold text-slate-950">ابدأ بهذه الطريقة بدل البحث العشوائي</h2>

        <ol class="mt-6 grid gap-4 lg:grid-cols-3">
            <li class="rounded-2xl bg-slate-50 p-5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 font-extrabold text-brand-800">1</span>
                <h3 class="mt-3 font-extrabold text-slate-900">افهم نفسك</h3>
                <p class="mt-2 leading-7 text-slate-600">راجع ميولك، الأنشطة التي تستمتع بها، قدراتك التي تريد تطويرها، قيمك وأهدافك، وما تعرفه فعلًا عن نفسك من التجربة.</p>
            </li>
            <li class="rounded-2xl bg-slate-50 p-5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 font-extrabold text-brand-800">2</span>
                <h3 class="mt-3 font-extrabold text-slate-900">افهم الخيارات</h3>
                <p class="mt-2 leading-7 text-slate-600">اقرأ طبيعة الدراسة، المواد والمهارات المطلوبة، الأنشطة داخل التخصص، والمسارات المهنية بدل الاعتماد على اسم التخصص فقط.</p>
            </li>
            <li class="rounded-2xl bg-slate-50 p-5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 font-extrabold text-brand-800">3</span>
                <h3 class="mt-3 font-extrabold text-slate-900">قارن بوعي</h3>
                <p class="mt-2 leading-7 text-slate-600">ابحث عن نقاط التقارب والتعارض، ثم تحقق من الخيارات الأقرب إليك بقراءة أعمق أو تجربة صغيرة أو سؤال شخص لديه خبرة حقيقية.</p>
            </li>
        </ol>
    </section>

    <section class="mt-6" aria-labelledby="factors-title">
        <h2 id="factors-title" class="text-2xl font-extrabold text-slate-950">ما العوامل التي أضعها أمامي قبل اختيار التخصص؟</h2>

        <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">ميولك</h3>
                <p class="mt-2 leading-7 text-slate-600">ما الأنشطة التي تستمتع بها وتريد تكرارها؟ الميول نقطة بداية، وليست حكمًا نهائيًا على مستقبلك.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">قدراتك واستعدادك للتعلم</h3>
                <p class="mt-2 leading-7 text-slate-600">لا تسأل فقط: «هل أنا جيد الآن؟» بل أيضًا: «هل أنا مستعد لتعلم المهارات التي يتطلبها هذا التخصص؟»</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">طبيعة الدراسة</h3>
                <p class="mt-2 leading-7 text-slate-600">افهم كيف ستتعلم فعليًا: قراءة، تحليل، مختبر، تدريب عملي، تصميم، تواصل، مشاريع أو عمل ميداني.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">قيمك وأهدافك</h3>
                <p class="mt-2 leading-7 text-slate-600">ما الذي تريد أن يكون مهمًا في حياتك ودراستك وعملك؟ لا تجعل قرارك مجرد استجابة لرأي الآخرين.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">الظروف الواقعية</h3>
                <p class="mt-2 leading-7 text-slate-600">ضع في الحسبان الجامعة المتاحة، التكلفة، المكان، متطلبات القبول، الوقت والقيود التي تحتاج إلى خطة أو بديل.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-extrabold text-slate-900">المعلومة والتجربة</h3>
                <p class="mt-2 leading-7 text-slate-600">كلما عرفت التخصص من مصادر موثوقة وجرّبت نشاطًا قريبًا منه، أصبح قرارك مبنيًا على معرفة أكثر وتخمين أقل.</p>
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-5 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-slate-950">محتار أي تخصص أدخل؟ لا تبدأ بهذه الأخطاء</h2>
            <ul class="mt-4 space-y-3 leading-7 text-slate-600">
                <li>اختيار التخصص لأن اسمه مشهور فقط.</li>
                <li>الاعتماد على الراتب أو المكانة وحدهما.</li>
                <li>ترك شخص آخر يقرر بدلًا عنك دون فهمك لطبيعة الاختيار.</li>
                <li>اعتبار نتيجة اختبار واحد إجابة نهائية لا تحتاج إلى تحقق.</li>
                <li>رفض تخصص قبل معرفة مواده وأنشطته ومساراته الفعلية.</li>
            </ul>
        </div>

        <div class="rounded-3xl border border-brand-200 bg-brand-50 p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-slate-950">هل اختبار الميول يكفي لاختيار التخصص؟</h2>
            <p class="mt-3 leading-8 text-slate-700">
                لا. اختبار الميول يساعدك على فهم المجالات والأنشطة التي تجذبك أكثر، لكنه لا يقيس كل شيء ولا يقرر عنك. استخدم نتيجته مع فهم طبيعة الدراسة وقدراتك وقيمك وظروفك الواقعية.
            </p>
            <a href="{{ route('career-interests.index') }}" class="mt-5 inline-flex min-h-11 items-center font-extrabold text-brand-800 hover:text-brand-900">
                تعرّف إلى اختبار الميول المهنية
            </a>
        </div>
    </section>

    <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="next-step-title">
        <h2 id="next-step-title" class="text-2xl font-extrabold text-slate-950">خطوتك التالية: حوّل الحيرة إلى مقارنة</h2>
        <p class="mt-3 max-w-4xl leading-8 text-slate-600">
            اختر تخصصين أو ثلاثة يبدون قريبين منك، واقرأ لكل واحد طبيعة الدراسة والمهارات والأنشطة والمسارات المهنية. سجّل ما يجذبك وما يقلقك، ثم ارجع إلى ميولك وظروفك بدل الاختيار بالانطباع الأول.
        </p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <a href="{{ route('specializations.index') }}" class="btn btn-primary btn-pill min-h-12 px-6 text-base font-extrabold">
                افتح دليل التخصصات الجامعية
            </a>
            <a href="{{ route('career-interests.index') }}" class="btn btn-secondary btn-pill min-h-12 px-6 text-base font-bold">
                استكشف ميولك أولًا
            </a>
        </div>
    </section>
</article>
@endsection
