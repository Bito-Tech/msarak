<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    @php
        $seoSiteName = (string) config('seo.site_name', 'مسارك');
        $seoPageTitle = trim($__env->yieldContent('title'));
        $seoTitle = $seoPageTitle !== '' ? $seoPageTitle.' | '.$seoSiteName : $seoSiteName;
        $seoDescription = trim($__env->yieldContent(
            'seo_description',
            (string) config('seo.default_description', '')
        ));
        $seoCanonical = trim($__env->yieldContent('seo_canonical', url()->current()));
        $seoType = trim($__env->yieldContent(
            'seo_type',
            (string) config('seo.default_type', 'website')
        ));
        $seoImage = trim($__env->yieldContent('seo_image'));
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ request()->routeIs('assessment.*') || request()->routeIs('home') || request()->routeIs('specializations.*') || request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*') ? '#0f766e' : '#4f46e5' }}">

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $seoCanonical }}">

    <meta property="og:site_name" content="{{ $seoSiteName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:locale" content="{{ config('seo.locale', 'ar_YE') }}">
    @if ($seoImage !== '')
        <meta property="og:image" content="{{ $seoImage }}">
    @endif

    <meta name="twitter:card" content="{{ $seoImage !== '' ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    @if ($seoImage !== '')
        <meta name="twitter:image" content="{{ $seoImage }}">
    @endif

    {{-- الخط العربي المعتمد: IBM Plex Sans Arabic ذو الرصانة التقنية والوضوح العالي في الواجهات البرمجية --}}
    <link rel="preload" as="font" type="font/woff2" href="/fonts/ibm-plex-sans-arabic-arabic-400-normal.woff2" crossorigin>
    <link rel="icon" type="image/svg+xml" href="/assets/brand/masarak-logo.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased font-sans {{ request()->routeIs('assessment.*') ? 'assessment-page' : '' }} {{ request()->routeIs('assessment.show') ? 'assessment-question-page' : '' }} {{ request()->routeIs('home') ? 'home-page' : '' }} {{ request()->routeIs('specializations.index') ? 'specializations-index-page' : '' }} {{ request()->routeIs('specializations.show') ? 'specialization-detail-page' : '' }} {{ request()->routeIs('specializations.compare') ? 'specialization-compare-page' : '' }} {{ request()->routeIs('results.show') ? 'result-show-page' : '' }} {{ request()->routeIs('profile.results.index') ? 'results-history-page' : '' }} {{ request()->routeIs('profile.show') ? 'profile-page' : '' }} {{ request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*') ? 'auth-page' : '' }}">
    <a href="#main" class="skip-link">تخطي إلى المحتوى الرئيسي</a>

    <header class="site-header sticky top-0 z-40 bg-white/88 backdrop-blur-md">
        @php
            // روابط التنقل — مصدر واحد يُعرض مضمّنًا على المكتب ومنسدلًا على الجوال.
            // النصوص والـaria-current مطابقة حرفيًا لما قبل التحويل.
            $navLinks = [
                ['href' => url('/'), 'label' => 'الرئيسية', 'current' => request()->is('/')],
                ['href' => route('specializations.index'), 'label' => 'التخصصات', 'current' => request()->routeIs('specializations.*')],
            ];
            if (auth()->check() && auth()->user()->role === 'student') {
                $navLinks[] = ['href' => route('assessment.intro'), 'label' => 'استكشاف ميولك', 'current' => request()->routeIs('assessment.*')];
                $navLinks[] = ['href' => route('profile.results.index'), 'label' => 'سجل نتائجي', 'current' => request()->routeIs('results.*') || request()->routeIs('profile.results.*')];
                $navLinks[] = ['href' => route('profile.show'), 'label' => 'حسابي', 'current' => request()->routeIs('profile.show')];
            } elseif (auth()->check() && auth()->user()->role === 'admin') {
                $navLinks[] = ['href' => route('admin.assessment-versions.index'), 'label' => 'إدارة التقييم', 'current' => request()->routeIs('admin.assessment-versions.*')];
                $navLinks[] = ['href' => route('admin.statistics.index'), 'label' => 'الإحصائيات', 'current' => request()->routeIs('admin.statistics.*')];
            } elseif (! auth()->check()) {
                $navLinks[] = ['href' => route('login'), 'label' => 'تسجيل الدخول', 'current' => request()->routeIs('login')];
            }
        @endphp
        <div class="site-header-shell flex w-full items-center justify-between gap-x-4 py-2.5 lg:gap-x-6">
            <a href="{{ url('/') }}" class="site-header-brand inline-flex min-h-11 items-center gap-2.5 text-xl font-extrabold transition-colors">
                <img src="/assets/brand/masarak-logo.svg"
                     alt=""
                     class="site-header-logo h-10 w-10 shrink-0 object-contain"
                     width="40"
                     height="40">
                <span>مسارك</span>
            </a>

            <nav aria-label="التنقل الرئيسي" class="relative shrink-0">
                {{-- The full navigation fits reliably at desktop widths. --}}
                <ul class="hidden items-center gap-x-2 lg:flex xl:gap-x-4">
                    @foreach ($navLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}"
                               class="nav-link inline-flex min-h-11 items-center rounded-lg px-2.5 font-medium text-slate-700 transition-colors hover:bg-brand-50 hover:text-brand-700"
                               @if ($link['current']) aria-current="page" @endif>{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                    @auth
                        {{-- UX-02: تسجيل الخروج بحوار تأكيد CSS-only (checkbox مخفي + peer-checked)
                             — بلا JS وبلا تعديل مسارات: التأكيد submit فعلي، والإلغاء label يفك التحبير --}}
                        <li class="relative">
                            <input type="checkbox" id="logout-pop-desktop" class="peer sr-only">
                            <label for="logout-pop-desktop"
                                   class="nav-link inline-flex min-h-11 cursor-pointer select-none items-center rounded-lg px-2.5 font-medium text-slate-700 transition-colors hover:bg-danger-50 hover:text-danger-700 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-600/15">تسجيل الخروج</label>
                            <form method="POST" action="{{ route('logout') }}"
                                  class="absolute end-0 top-full z-50 mt-1 hidden w-64 rounded-xl border border-slate-200 bg-white p-4 text-start shadow-pop peer-checked:block">
                                @csrf
                                <p class="text-sm font-bold text-slate-900">هل تريد تسجيل الخروج؟</p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">ستحتاج إلى تسجيل الدخول مجددًا للعودة إلى حسابك.</p>
                                <div class="mt-4 flex items-center justify-end gap-2">
                                    <label for="logout-pop-desktop"
                                           class="btn btn-secondary btn-pill text-sm cursor-pointer select-none">إلغاء</label>
                                    <button type="submit"
                                            class="btn btn-pill text-sm bg-danger-600 text-white shadow-sm shadow-danger-600/25 hover:bg-danger-700">تسجيل الخروج</button>
                                </div>
                            </form>
                        </li>
                    @endauth
                    {{-- روابط الصفحات اللاحقة تضاف هنا --}}
                </ul>

                {{-- Compact navigation for phones and tablets. --}}
                <details class="lg:hidden">
                    <summary class="nav-link flex min-h-11 cursor-pointer list-none select-none items-center gap-1.5 rounded-lg px-3 font-medium text-slate-700 transition-colors hover:bg-brand-50 hover:text-brand-700 [&::-webkit-details-marker]:hidden">
                        <x-ui.icon name="menu" class="h-5 w-5" />
                        القائمة
                    </summary>
                    <ul class="absolute end-0 top-full z-50 mt-2 w-64 max-w-[calc(100vw-2rem)] rounded-xl border border-slate-200 bg-white p-2 shadow-pop">
                        @foreach ($navLinks as $link)
                            <li>
                                <a href="{{ $link['href'] }}"
                                   class="nav-link flex min-h-11 items-center rounded-lg px-3 font-medium text-slate-700 transition-colors hover:bg-brand-50 hover:text-brand-700"
                                   @if ($link['current']) aria-current="page" @endif>{{ $link['label'] }}</a>
                            </li>
                        @endforeach
                        @auth
                            <li class="mt-1 border-t border-slate-100 pt-1">
                                {{-- نفس حوار التأكيد CSS-only — منسدل مضمّن داخل قائمة الجوال --}}
                                <input type="checkbox" id="logout-pop-mobile" class="peer sr-only">
                                <label for="logout-pop-mobile"
                                       class="nav-link flex min-h-11 cursor-pointer select-none items-center rounded-lg px-3 font-medium text-slate-700 transition-colors hover:bg-danger-50 hover:text-danger-700 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-600/15">تسجيل الخروج</label>
                                <form method="POST" action="{{ route('logout') }}"
                                      class="mt-1 hidden rounded-xl border border-slate-200 bg-slate-50 p-3 text-start peer-checked:block">
                                    @csrf
                                    <p class="text-sm font-bold text-slate-900">هل تريد تسجيل الخروج؟</p>
                                    <div class="mt-3 flex items-center justify-end gap-2">
                                        <label for="logout-pop-mobile"
                                               class="btn btn-secondary btn-pill text-sm cursor-pointer select-none">إلغاء</label>
                                        <button type="submit"
                                                class="btn btn-pill text-sm bg-danger-600 text-white shadow-sm shadow-danger-600/25 hover:bg-danger-700">تسجيل الخروج</button>
                                    </div>
                                </form>
                            </li>
                        @endauth
                    </ul>
                </details>
            </nav>
        </div>
        <div class="border-b border-slate-200" aria-hidden="true"></div>
    </header>

    <main id="main" tabindex="-1"
          class="{{ request()->routeIs('assessment.show')
              ? 'w-full flex-1 break-words px-2 py-2 sm:px-4 sm:py-3 lg:px-5 lg:py-3'
              : (request()->routeIs('assessment.intro')
                  ? 'w-full flex-1 break-words px-2 py-2 sm:px-4 sm:py-3 lg:px-5 lg:py-3'
                  : (request()->routeIs('home') || request()->routeIs('specializations.index') || request()->routeIs('specializations.show') || request()->routeIs('specializations.compare') || request()->routeIs('results.show') || request()->routeIs('profile.results.index') || request()->routeIs('profile.show') || request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*')
                      ? 'w-full flex-1 break-words px-2 py-2 sm:px-4 sm:py-3 lg:px-5 lg:py-5'
                      : 'container-page flex-1 break-words py-8 lg:py-12')) }}">
        <x-ui.flash class="mb-6" />
        @yield('content')
    </main>

    <footer class="site-footer mt-auto">
        <div class="site-footer-shell">
            <div class="site-footer-accent" aria-hidden="true"></div>

            <div class="container-page site-footer-inner py-7 sm:py-8">
                <div class="site-footer-grid grid gap-7 md:grid-cols-[1.25fr_0.8fr_0.95fr] md:items-start">
                    <div class="site-footer-brand min-w-0">
                        <div class="flex items-center gap-3">
                            <span class="site-footer-logo inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                                <img src="/assets/brand/masarak-logo.svg" alt="" class="h-9 w-9 object-contain">
                            </span>
                            <div>
                                <span class="site-footer-title block text-lg font-extrabold">مسارك</span>
                                <span class="site-footer-subtitle block text-xs font-bold">اختر بوعي أكبر</span>
                            </div>
                        </div>

                        <p class="site-footer-copy mt-3 max-w-md text-sm leading-relaxed">
                            منصة للتوجيه الأكاديمي والمهني تساعد طلاب الثانوية في اليمن على استكشاف ميولهم وفهم خياراتهم بصورة أوضح.
                        </p>
                    </div>

                    <section class="site-footer-about" aria-labelledby="footer-about-title">
                        <p id="footer-about-title" class="site-footer-heading text-sm font-extrabold">من نحن</p>
                        <p class="site-footer-team mt-2 text-base font-extrabold">فريق بيتو تك</p>

                        <div class="site-footer-social mt-3 flex items-center gap-2.5">
                            <a href="https://github.com/Bito-Tech"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="site-footer-social-link"
                               aria-label="فريق بيتو تك على GitHub"
                               title="GitHub">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" d="M12 .7a11.3 11.3 0 0 0-3.57 22.02c.57.1.78-.25.78-.55v-2.17c-3.18.69-3.85-1.35-3.85-1.35-.52-1.32-1.27-1.67-1.27-1.67-1.04-.71.08-.7.08-.7 1.15.08 1.75 1.18 1.75 1.18 1.02 1.75 2.68 1.24 3.33.95.1-.74.4-1.24.73-1.53-2.54-.29-5.21-1.27-5.21-5.65 0-1.25.45-2.27 1.18-3.07-.12-.29-.51-1.45.11-3.02 0 0 .96-.31 3.12 1.17A10.85 10.85 0 0 1 12 5.93c.96 0 1.93.13 2.83.38 2.16-1.48 3.12-1.17 3.12-1.17.62 1.57.23 2.73.11 3.02.73.8 1.18 1.82 1.18 3.07 0 4.39-2.68 5.35-5.23 5.64.41.35.77 1.04.77 2.1v3.2c0 .3.21.66.79.55A11.3 11.3 0 0 0 12 .7Z"/>
                                </svg>
                            </a>

                            <a href="https://www.linkedin.com/company/bito-tech"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="site-footer-social-link"
                               aria-label="فريق بيتو تك على LinkedIn"
                               title="LinkedIn">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" d="M5.37 7.97H1.75V19.5h3.62V7.97ZM3.56 2.5A2.1 2.1 0 1 0 3.56 6.7a2.1 2.1 0 0 0 0-4.2ZM22.25 12.89c0-3.47-1.85-5.08-4.32-5.08-1.99 0-2.88 1.09-3.38 1.86v-1.7h-3.62c.05 1.13 0 11.53 0 11.53h3.62v-6.44c0-.34.03-.69.13-.94.25-.69.82-1.4 1.77-1.4 1.25 0 1.75.95 1.75 2.35v6.43h3.62l.43-6.61Z"/>
                                </svg>
                            </a>
                        </div>
                    </section>

                    <nav aria-label="روابط التذييل" class="site-footer-nav">
                        <p class="site-footer-heading text-sm font-extrabold">روابط سريعة</p>
                        <ul class="site-footer-links mt-2.5 flex flex-wrap gap-1.5 text-sm font-semibold">
                            <li>
                                <a href="{{ url('/') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-xl px-3">الرئيسية</a>
                            </li>
                            <li>
                                <a href="{{ route('specializations.index') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-xl px-3">التخصصات</a>
                            </li>
                            @auth
                                @if (auth()->user()->role === 'student')
                                    <li>
                                        <a href="{{ route('assessment.intro') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-xl px-3">استكشاف ميولك</a>
                                    </li>
                                @endif
                                @if (auth()->user()->role === 'admin')
                                    <li>
                                        <a href="{{ route('admin.assessment-versions.index') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-xl px-3">إدارة التقييم</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.statistics.index') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-xl px-3">الإحصائيات</a>
                                    </li>
                                @endif
                            @endauth
                            @guest
                                <li>
                                    <a href="{{ route('login') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-xl px-3">تسجيل الدخول</a>
                                </li>
                                <li>
                                    <a href="{{ route('register') }}" class="site-footer-link site-footer-link-cta inline-flex min-h-10 items-center rounded-xl px-3">إنشاء حساب</a>
                                </li>
                            @endguest
                        </ul>
                    </nav>
                </div>

                <div class="site-footer-bottom mt-6 flex flex-col gap-2 border-t pt-4 text-xs sm:flex-row sm:items-center sm:justify-between">
                    <span>مسارك — أحد أعمال فريق بيتو تك</span>
                    <span>منصة توجيه أكاديمي ومهني لطلاب الثانوية في اليمن</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
