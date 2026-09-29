@extends('layouts.app')

@section('title', 'استعادة كلمة المرور')

@section('content')
<section class="auth-experience auth-experience-compact mx-auto w-full" dir="rtl" aria-labelledby="forgot-title">
    <div class="auth-frame overflow-hidden rounded-[1.7rem] border">
        <div class="grid lg:grid-cols-[0.92fr_1.08fr]">
            <section class="auth-form-panel order-2 p-5 sm:p-8 lg:order-1 lg:p-10">
                <div class="auth-brand-mobile mb-6 flex items-center gap-3 lg:hidden">
                    <img src="/assets/brand/masarak-logo.svg" alt="" class="h-11 w-11 object-contain">
                    <div>
                        <strong class="block text-lg font-extrabold">مسارك</strong>
                        <span class="block text-xs">استعادة الوصول إلى حسابك</span>
                    </div>
                </div>

                <div class="auth-copy">
                    <p class="auth-eyebrow text-sm font-extrabold">استعادة الوصول</p>
                    <h1 id="forgot-title" class="auth-title mt-1 font-extrabold">نسيت كلمة المرور؟</h1>
                    <p class="auth-lead mt-2">أدخل بريدك الإلكتروني، وإذا كان مسجلًا لدينا سنرسل لك رابطًا لتعيين كلمة مرور جديدة.</p>
                </div>

                <form method="POST" action="{{ route('password.email') }}" class="auth-form auth-form-v2 mt-7 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="field-label auth-field-label">البريد الإلكتروني</label>
                        <input
                            id="email" name="email" type="email"
                            value="{{ old('email') }}"
                            required autofocus autocomplete="email"
                            class="field-input auth-field-input ltr-text"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                        >
                        @error('email')
                            <p id="email-error" class="auth-field-error mt-1.5 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="auth-primary-button inline-flex min-h-14 w-full items-center justify-center rounded-xl px-5 font-extrabold">
                        إرسال رابط الاستعادة
                    </button>
                </form>

                <div class="auth-switch mt-6 border-t pt-5 text-center">
                    <a href="{{ route('login') }}" class="auth-inline-link font-extrabold">العودة إلى تسجيل الدخول</a>
                </div>
            </section>

            <aside class="auth-story order-1 hidden lg:flex lg:flex-col lg:justify-between" aria-hidden="true">
                <div>
                    <div class="auth-story-brand flex items-center gap-3">
                        <span class="auth-story-logo inline-flex h-14 w-14 items-center justify-center rounded-2xl">
                            <img src="/assets/brand/masarak-logo.svg" alt="" class="h-11 w-11 object-contain">
                        </span>
                        <div>
                            <strong class="block text-xl font-extrabold">مسارك</strong>
                            <span class="block text-sm">اختر بوعي أكبر</span>
                        </div>
                    </div>

                    <div class="auth-story-copy mt-12">
                        <p class="auth-story-kicker text-sm font-extrabold">خطوة بسيطة</p>
                        <h2 class="auth-story-title mt-2 font-extrabold">استعد حسابك، ثم أكمل رحلتك بشكل طبيعي.</h2>
                        <p class="auth-story-text mt-4">لن نطلب منك أكثر من بريدك الإلكتروني في هذه الخطوة.</p>
                    </div>
                </div>

                <p class="auth-story-footnote">ستظهر لك رسالة تأكيد بعد إرسال الطلب سواء كان البريد مسجلًا أم لا.</p>
            </aside>
        </div>
    </div>
</section>
@endsection
