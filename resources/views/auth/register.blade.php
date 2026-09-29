@extends('layouts.app')

@section('title', 'إنشاء حساب')

@section('content')
<section class="auth-experience mx-auto w-full" dir="rtl" aria-labelledby="register-title">
    <div class="auth-frame overflow-hidden rounded-[1.7rem] border">
        <div class="grid lg:grid-cols-[0.92fr_1.08fr]">
            <section class="auth-form-panel order-2 p-5 sm:p-8 lg:order-1 lg:p-9">
                <div class="auth-brand-mobile mb-5 flex items-center gap-3 lg:hidden">
                    <img src="/assets/brand/masarak-logo.svg" alt="" class="h-11 w-11 object-contain">
                    <div>
                        <strong class="block text-lg font-extrabold">مسارك</strong>
                        <span class="block text-xs">ابدأ من فهم أفضل لنفسك</span>
                    </div>
                </div>

                <div class="auth-copy">
                    <p class="auth-eyebrow text-sm font-extrabold">خطوة واحدة للبدء</p>
                    <h1 id="register-title" class="auth-title mt-1 font-extrabold">إنشاء حساب</h1>
                    <p class="auth-lead mt-2">أنشئ حسابك لحفظ تقدمك ونتائجك والعودة إليها لاحقًا.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="auth-form auth-form-v2 mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="field-label auth-field-label">الاسم</label>
                        <input
                            id="name" name="name" type="text"
                            value="{{ old('name') }}"
                            required autofocus autocomplete="name"
                            class="field-input auth-field-input"
                            @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                        >
                        @error('name')
                            <p id="name-error" class="auth-field-error mt-1.5 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="field-label auth-field-label">البريد الإلكتروني</label>
                        <input
                            id="email" name="email" type="email"
                            value="{{ old('email') }}"
                            required autocomplete="email"
                            class="field-input auth-field-input ltr-text"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                        >
                        @error('email')
                            <p id="email-error" class="auth-field-error mt-1.5 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="field-label auth-field-label">كلمة المرور <span class="auth-field-note">(8 محارف على الأقل)</span></label>
                        <div class="field-wrap">
                            <input
                                id="password" name="password" type="password"
                                required autocomplete="new-password"
                                class="field-input field-input-with-toggle auth-field-input ltr-text"
                                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                            >
                            <button type="button" class="field-toggle auth-field-toggle" data-password-toggle data-target="password"
                                    aria-pressed="false" aria-label="إظهار كلمة المرور">
                                <svg data-icon-show class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg data-icon-hide class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="auth-field-error mt-1.5 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="field-label auth-field-label">تأكيد كلمة المرور</label>
                        <div class="field-wrap">
                            <input
                                id="password_confirmation" name="password_confirmation" type="password"
                                required autocomplete="new-password"
                                class="field-input field-input-with-toggle auth-field-input ltr-text"
                            >
                            <button type="button" class="field-toggle auth-field-toggle" data-password-toggle data-target="password_confirmation"
                                    aria-pressed="false" aria-label="إظهار كلمة المرور">
                                <svg data-icon-show class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg data-icon-hide class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="auth-primary-button inline-flex min-h-14 w-full items-center justify-center rounded-xl px-5 font-extrabold">
                        إنشاء الحساب
                    </button>
                </form>

                <div class="auth-switch mt-5 border-t pt-4 text-center">
                    <p>لديك حساب؟ <a href="{{ route('login') }}" class="auth-inline-link font-extrabold">سجّل الدخول</a></p>
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

                    <div class="auth-story-copy mt-10">
                        <p class="auth-story-kicker text-sm font-extrabold">ابدأ بهدوء</p>
                        <h2 class="auth-story-title mt-2 font-extrabold">حساب واحد يكفي لرحلة الاستكشاف كاملة.</h2>
                        <p class="auth-story-text mt-4">أنشئ حسابك مرة واحدة، ثم احتفظ بتقدمك ونتائجك ومقارناتك في مكان واضح.</p>
                    </div>
                </div>

                <div class="auth-story-list grid gap-3">
                    <div class="auth-story-item flex items-center gap-3 rounded-2xl px-4 py-3">
                        <span>01</span><strong>استكشف ميولك</strong>
                    </div>
                    <div class="auth-story-item flex items-center gap-3 rounded-2xl px-4 py-3">
                        <span>02</span><strong>احتفظ بنتيجتك</strong>
                    </div>
                    <div class="auth-story-item flex items-center gap-3 rounded-2xl px-4 py-3">
                        <span>03</span><strong>ارجع إليها لاحقًا</strong>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
