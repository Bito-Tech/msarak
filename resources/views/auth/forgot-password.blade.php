@extends('layouts.app')

@section('title', 'استعادة كلمة المرور')

@section('content')
<section class="auth-canvas" dir="rtl" aria-labelledby="forgot-title">
    <div class="auth-split">
        <aside class="auth-visual-panel" aria-hidden="true">
            <div class="auth-visual-copy">
                <h2>اختر طريقك بثقة</h2>
                <span class="auth-visual-mark"></span>
                <p>مسارك يساعدك على استكشاف ميولك<br>وبناء مستقبلك بخطوات واضحة.</p>
            </div>
        </aside>

        <section class="auth-form-panel-v3">
            <div class="auth-form-inner auth-form-inner-compact">
                <a href="{{ route('home') }}" class="auth-logo-block" aria-label="العودة إلى مسارك">
                    <img src="/assets/brand/masarak-logo.svg" alt="" class="auth-logo-image">
                    <span>مسارك</span>
                </a>

                <div class="auth-form-heading">
                    <h1 id="forgot-title">استعادة كلمة المرور</h1>
                    <p>أدخل بريدك الإلكتروني، وسنرسل لك رابطًا لاستعادة الوصول إلى حسابك.</p>
                </div>

                <form method="POST" action="{{ route('password.email') }}" class="auth-form-v3">
                    @csrf

                    <div class="auth-field-group">
                        <label for="email">البريد الإلكتروني</label>
                        <div class="auth-input-wrap">
                            <input id="email" name="email" type="email"
                                   value="{{ old('email') }}"
                                   required autofocus autocomplete="email"
                                   class="auth-input ltr-text"
                                   placeholder="أدخل بريدك الإلكتروني"
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="envelope" class="h-5 w-5" /></span>
                        </div>
                        @error('email')<p id="email-error" class="auth-error">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="auth-submit">
                        <span>إرسال رابط الاستعادة</span>
                        <x-ui.icon name="arrow-end" class="h-5 w-5" />
                    </button>
                </form>

                <div class="auth-bottom-links auth-bottom-links-single">
                    <a href="{{ route('login') }}">العودة إلى تسجيل الدخول</a>
                </div>
            </div>
        </section>
    </div>
</section>
@endsection
