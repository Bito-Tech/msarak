@extends('layouts.app')

@section('title', 'اختبار الميول المهنية بالعربي | اكتشف ميولك')
@section('seo_description', 'اختبار الميول المهنية في مسارك يساعد طلاب الثانوية على استكشاف ميولهم وفهم مجالات الاهتمام قبل اختيار التخصص الجامعي بوعي أكبر.')

@section('content')
<article class="mx-auto w-full max-w-6xl" dir="rtl">
    <nav aria-label="مسار الصفحة" class="mb-4 text-sm font-semibold text-slate-500">
        <a href="{{ route('home') }}" class="transition-colors hover:text-brand-700">الرئيسية</a>
        <span class="mx-2" aria-hidden="true">/</span>
        <span aria-current="page" class="text-slate-700">اختبار الميول المهنية</span>
    </nav>

    <header class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
        <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
            <div class="flex flex-col justify-center p-6 sm:p-9 lg:p-12">
                <p class="text-sm font-extrabold text-brand-700">استكشاف إرشادي لطلاب الثانوية</p>
                <h1 class="mt-3 text-3xl font-extrabold leading-tight text-slate-950 sm:text-4xl lg:text-5xl">
                    اختبار الميول المهنية: ابدأ بفهم ما يجذبك
                </h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600 sm:text-lg">
                    يساعدك مسارك على ملاحظة الأنشطة والبيئات التي تميل إليها أكثر، ثم يوضح لك مجالات الاهتمام الأقرب إلى إجاباتك لتستكشف تخصصاتك الجامعية بوعي أكبر.
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-primary btn-pill min-h-12 px-6 text-base font-extrabold">
                            ابدأ استكشاف ميولك
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-pill min-h-12 px-6 text-base font-bold">
                            لدي حساب
                        </a>
                    @else
                        @if (auth()->user()->role === 'student')
                            <a href="{{ route('assessment.intro') }}" class="btn btn-primary btn-pill min-h-12 px-6 text-base font-extrabold">
                                ابدأ استكشاف ميولك
                            </a>
                        @else
                            <a href="{{ route('specializations.index') }}" class="btn btn-primary btn-pill min-h-12 px-6 text-base font-extrabold">
                                تصفح دليل التخصصات
                            </a>
                        @endif
                    @endguest
                </div>

                <p class="mt-5 max-w-3xl text-sm leading-7 text-slate-500">
                    يعتمد تقييم مسارك الإرشادي على نموذج هولاند RIASEC لاستكشاف الميول المهنية، وليس لاختبار الشخصية أو القدرات، ولا يقرر عنك تخصصًا أو مهنة نهائية.
                </p>
            </div>

            <div class="relative min-h-72 overflow-hidden bg-brand-50 lg:min-h-[28rem]">
                <img
                    src="/assets/home/masarak-home-student-hero.jpg"
                    alt="طالب يستكشف ميوله وخياراته بعد الثانوية"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="eager"
                    fetchpriority="high">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/20 via-transparent to-transparent" aria-hidden="true"></div>
            </div>
        </div>
    </header>

    <section class="mt-6 grid gap-4 md:grid-cols-3" aria-labelledby="what-title">
        <div class="md:col-span-3">
            <h2 id="what-title" class="text-2xl font-extrabold text-slate-950">ماذا يساعدك الاختبار على اكتشافه؟</h2>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 class="font-extrabold text-slate-900">الأنشطة التي تفضّلها</h3>
            <p class="mt-2 leading-7 text-slate-600">هل تنجذب أكثر للتحليل، التطبيق العملي، الإبداع، مساعدة الآخرين، المبادرة أم التنظيم؟</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 class="font-extrabold text-slate-900">مجالات اهتمامك</h3>
            <p class="mt-2 leading-7 text-slate-600">تُقرأ إجاباتك عبر ستة مجالات للميول المهنية في نموذج هولاند RIASEC: عملي/تطبيقي، بحثي/تحليلي، فني/إبداعي، اجتماعي/مساند، مبادر/تأثيري، وتنظيمي/إجرائي.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 class="font-extrabold text-slate-900">خيارات تستحق الاستكشاف</h3>
            <p class="mt-2 leading-7 text-slate-600">بعد فهم نمط ميولك يمكنك الانتقال إلى دليل التخصصات ومقارنة طبيعة الدراسة والمهارات والمسارات المهنية.</p>
        </div>
    </section>

    <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="how-title">
        <h2 id="how-title" class="text-2xl font-extrabold text-slate-950">كيف يعمل استكشاف الميول في مسارك؟</h2>

        <ol class="mt-6 grid gap-4 lg:grid-cols-3">
            <li class="rounded-2xl bg-slate-50 p-5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 font-extrabold text-brand-800">1</span>
                <h3 class="mt-3 font-extrabold text-slate-900">أجب عن المواقف</h3>
                <p class="mt-2 leading-7 text-slate-600">اختر الإجابة الأقرب إليك كما أنت الآن، من غير محاولة الوصول إلى نتيجة معينة.</p>
            </li>
            <li class="rounded-2xl bg-slate-50 p-5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 font-extrabold text-brand-800">2</span>
                <h3 class="mt-3 font-extrabold text-slate-900">افهم نمط ميولك</h3>
                <p class="mt-2 leading-7 text-slate-600">يعرض مسارك المجالات التي ظهرت فيها ميول أعلى في إجاباتك بدل وضعك في قالب شخصي ثابت.</p>
            </li>
            <li class="rounded-2xl bg-slate-50 p-5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 font-extrabold text-brand-800">3</span>
                <h3 class="mt-3 font-extrabold text-slate-900">استكشف التخصصات</h3>
                <p class="mt-2 leading-7 text-slate-600">استخدم النتيجة كنقطة بداية، ثم اقرأ عن التخصصات وقارن بينها مع مراعاة قدراتك وظروفك وأهدافك.</p>
            </li>
        </ol>
    </section>

    <section class="mt-6 grid gap-5 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-slate-950">هل اختبار الميول يختار تخصصي بدلًا عني؟</h2>
            <p class="mt-3 leading-8 text-slate-600">
                لا. الميول جزء مهم من القرار لكنها ليست العامل الوحيد. اختيار التخصص يحتاج أيضًا إلى النظر في القدرات، ومتطلبات الدراسة، والفرص المتاحة، والقيم الشخصية، والظروف الواقعية.
            </p>
        </div>

        <div class="rounded-3xl border border-brand-200 bg-brand-50 p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-slate-950">ماذا أفعل بعد معرفة ميولي؟</h2>
            <p class="mt-3 leading-8 text-slate-700">
                لا تتوقف عند النتيجة. افتح دليل التخصصات واقرأ طبيعة الدراسة والأنشطة والمهارات والمسارات المهنية، ثم قارن الخيارات التي تبدو أقرب لك.
            </p>
            <a href="{{ route('specializations.index') }}" class="mt-5 inline-flex min-h-11 items-center font-extrabold text-brand-800 hover:text-brand-900">
                تصفح دليل التخصصات الجامعية
            </a>
        </div>
    </section>
</article>
@endsection
