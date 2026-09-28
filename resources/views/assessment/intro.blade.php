@extends('layouts.app')

@section('title', 'استكشاف ميولك')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <header class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-bold text-brand-700">18 موقفًا · نحو 10 – 15 دقيقة</p>
        <h1 class="mt-3 text-3xl font-extrabold text-slate-900 sm:text-4xl">استكشاف ميولك</h1>
        <p class="mt-4 text-base leading-relaxed text-slate-700">يساعدك هذا التقييم على استكشاف الأنشطة والمجالات التي قد تستمتع بها أو ترغب في التعرف إليها أكثر.</p>
        <p class="mt-3 text-sm leading-relaxed text-slate-600">التقييم أداة استكشافية وإرشادية، وليس اختبارًا للقدرات أو تشخيصًا للشخصية، ولا يحدد تخصصًا أو مهنة واحدة مناسبة لك بشكل نهائي.</p>
    </header>

    @if ($activeSession)
        <section aria-labelledby="resume-heading" class="rounded-3xl border border-brand-200 bg-brand-50 p-6 sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="resume-heading" class="text-xl font-bold text-slate-900">لديك تقييم غير مكتمل</h2>
                    <p class="mt-1 text-sm leading-relaxed text-slate-700">تم حفظ تقدمك، ويمكنك المتابعة من حيث توقفت.</p>
                </div>
                <form method="POST" action="{{ route('assessment.sessions.store') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">متابعة التقييم</button>
                </form>
            </div>
        </section>
    @endif

    <section aria-labelledby="guide-heading" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h2 id="guide-heading" class="text-2xl font-bold text-slate-900">كيف تجيب؟</h2>
        <ol class="mt-6 space-y-7">
            <li class="flex gap-4">
                <span aria-hidden="true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-100 font-bold text-brand-800">1</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">اختر تصرفًا واحدًا يشبهك أكثر</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-700">في كل موقف، اقرأ التصرفات المعروضة واختر تصرفًا واحدًا فقط يمثل ما يشبهك أكثر.</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">لا توجد إجابة صحيحة أو خاطئة. اختر التصرف الذي يمثل طريقة تعاملك المعتادة، وليس التصرف الذي تظن أنه الأفضل أو الذي يتوقعه الآخرون منك.</p>
                </div>
            </li>
            <li class="flex gap-4">
                <span aria-hidden="true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-100 font-bold text-brand-800">2</span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-bold text-slate-900">قيّم التصرفات الأربعة إن رغبت</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-700">بعد اختيار التصرف الأساسي، يمكنك اختياريًا تقييم أي عدد من التصرفات السلوكية الأربعة باستخدام السلم من -2 إلى +2. لا يلزمك تقييمها كلها، ويمكنك ترك بعضها دون تقييم.</p>
                    <p class="mt-3 text-sm font-semibold text-slate-700">مستويات التقييم:</p>
                    <ul class="mt-2 flex flex-wrap gap-2 text-sm" aria-label="مستويات التقييم الاختياري">
                        <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-800">يشبهني جدًا <bdi dir="ltr">(+2)</bdi></li>
                        <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-800">يشبهني <bdi dir="ltr">(+1)</bdi></li>
                        <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-800">محايد / غير متأكد <bdi dir="ltr">(0)</bdi></li>
                        <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-800">لا يشبهني <bdi dir="ltr">(-1)</bdi></li>
                        <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-800">لا يشبهني إطلاقًا <bdi dir="ltr">(-2)</bdi></li>
                    </ul>
                </div>
            </li>
            <li class="flex gap-4">
                <span aria-hidden="true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-100 font-bold text-brand-800">3</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">إذا تعذرت عليك مطابقة التصرفات</h3>
                    <dl class="mt-2 space-y-3 text-sm leading-relaxed">
                        <div>
                            <dt class="font-bold text-slate-900">لا يشبهني أي من هذه التصرفات</dt>
                            <dd class="text-slate-700">إذا فهمت الموقف، لكن لم يشبهك أي من التصرفات الأربعة.</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-900">لا أستطيع الحكم على هذا الموقف</dt>
                            <dd class="text-slate-700">إذا لم تستطع فهم الموقف أو لم تملك معلومات كافية لتكوين إجابة موثوقة. استخدم هذا الخيار عندما يتعذر عليك الحكم فعلًا، وليس لمجرد التردد العادي بين التصرفات.</dd>
                        </div>
                    </dl>
                </div>
            </li>
        </ol>
    </section>

    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm leading-relaxed text-slate-700 sm:p-6">
        <p><strong class="text-slate-900">أثناء التقييم:</strong> تُحفظ إجاباتك تلقائيًا. تحقق من حالة الحفظ قبل مغادرة الصفحة، ويمكنك العودة أو مراجعة أي موقف.</p>
        <p class="mt-2">أجب وفق ما يشبهك أنت، ولا تحاول اختيار الإجابة التي تبدو أفضل أو أكثر قبولًا. خذ وقتك واقرأ كل موقف بهدوء.</p>
    </div>

    @unless ($activeSession)
        <form method="POST" action="{{ route('assessment.sessions.store') }}" class="pb-2">
            @csrf
            <button type="submit" class="btn btn-primary w-full sm:w-auto">بدء التقييم</button>
        </form>
    @endunless
</div>
@endsection
