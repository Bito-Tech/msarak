@extends('layouts.app')

@section('title', 'تعيين كلمة مرور جديدة')

@section('content')
<section class="auth-canvas" dir="rtl" aria-labelledby="reset-title">
    <div class="auth-split">
        <aside class="auth-visual-panel" aria-hidden="true">
            <div class="auth-visual-copy">
                <h2>اختر طريقك بثقة</h2>
                <span class="auth-visual-mark"></span>
                <p>مسارك يساعدك على استكشاف ميولك<br>وبناء مستقبلك بخطوات واضحة.</p>
            </div>
        </aside>

        <section class="auth-form-panel-v3">
            <div class="auth-form-inner auth-form-inner-register">
                <a href="{{ route('home') }}" class="auth-logo-block" aria-label="العودة إلى مسارك">
                    <img src="/assets/brand/masarak-logo.svg" alt="" class="auth-logo-image">
                    <span>مسارك</span>
                </a>

                <div class="auth-form-heading">
                    <h1 id="reset-title">تعيين كلمة مرور جديدة</h1>
                    <p>اختر كلمة مرور جديدة ثم استخدمها في تسجيل الدخول القادم.</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="auth-form-v3 auth-form-v3-register">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="auth-field-group">
                        <label for="email">البريد الإلكتروني</label>
                        <div class="auth-input-wrap">
                            <input id="email" name="email" type="email"
                                   value="{{ old('email', $email) }}"
                                   required autocomplete="email"
                                   class="auth-input ltr-text"
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="envelope" class="h-5 w-5" /></span>
                        </div>
                        @error('email')<p id="email-error" class="auth-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="auth-field-group">
                        <label for="password">كلمة المرور الجديدة</label>
                        <div class="auth-input-wrap">
                            <input id="password" name="password" type="password"
                                   required autocomplete="new-password"
                                   class="auth-input auth-input-with-toggle ltr-text"
                                   placeholder="8 محارف على الأقل"
                                   @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button type="button" class="auth-password-toggle" data-password-toggle data-target="password"
                                    aria-pressed="false" aria-label="إظهار كلمة المرور">
                                <svg data-icon-show class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                <svg data-icon-hide class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                        @error('password')<p id="password-error" class="auth-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="auth-field-group">
                        <label for="password_confirmation">تأكيد كلمة المرور</label>
                        <div class="auth-input-wrap">
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                   required autocomplete="new-password"
                                   class="auth-input auth-input-with-toggle ltr-text"
                                   placeholder="أعد إدخال كلمة المرور">
                            <button type="button" class="auth-password-toggle" data-password-toggle data-target="password_confirmation"
                                    aria-pressed="false" aria-label="إظهار كلمة المرور">
                                <svg data-icon-show class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                <svg data-icon-hide class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit">
                        <span>تغيير كلمة المرور</span>
                        <x-ui.icon name="arrow-end" class="h-5 w-5" />
                    </button>
                </form>
            </div>
        </section>
    </div>
</section>
@endsection
