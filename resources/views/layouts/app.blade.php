<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ request()->routeIs('assessment.*') || request()->routeIs('home') || request()->routeIs('specializations.index') ? '#0f766e' : '#4f46e5' }}">
    <title>@hasSection('title')@yield('title') | @endifمسارك</title>

    {{-- الخط العربي المعتمد: IBM Plex Sans Arabic ذو الرصانة التقنية والوضوح العالي في الواجهات البرمجية --}}
    <link rel="preload" as="font" type="font/woff2" href="/fonts/ibm-plex-sans-arabic-arabic-400-normal.woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased font-sans {{ request()->routeIs('assessment.*') ? 'assessment-page' : '' }} {{ request()->routeIs('home') ? 'home-page' : '' }} {{ request()->routeIs('specializations.index') ? 'specializations-index-page' : '' }}">
    <a href="#main" class="skip-link">تخطي إلى المحتوى الرئيسي</a>

    <header class="sticky top-0 z-40 bg-white/85 backdrop-blur-md">
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
        <div class="container-page flex items-center justify-between gap-x-4 py-2.5 lg:gap-x-6">
            <a href="{{ url('/') }}" class="inline-flex min-h-11 items-center gap-2 text-xl font-bold text-brand-700 transition-colors hover:text-brand-800">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-extrabold text-white shadow-sm shadow-brand-600/30" aria-hidden="true">م</span>
                مسارك
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
                  : (request()->routeIs('home') || request()->routeIs('specializations.index')
                      ? 'w-full flex-1 break-words px-2 py-2 sm:px-4 sm:py-3 lg:px-5 lg:py-5'
                      : 'container-page flex-1 break-words py-8 lg:py-12')) }}">
        <x-ui.flash class="mb-6" />
        @yield('content')
    </main>

    <footer class="site-footer mt-auto">
        <div class="site-footer-shell">
            <div class="site-footer-accent" aria-hidden="true"></div>

            <div class="container-page site-footer-inner flex flex-col gap-5 py-6 sm:flex-row sm:items-center sm:justify-between sm:gap-8">
                <div class="site-footer-brand min-w-0">
                    <div class="flex items-center gap-3">
                        <span class="site-footer-logo inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold" aria-hidden="true">م</span>
                        <div>
                            <span class="site-footer-title block text-lg font-extrabold">مسارك</span>
                            <span class="site-footer-subtitle block text-xs font-medium">اختر بوعي أكبر</span>
                        </div>
                    </div>

                    <p class="site-footer-copy mt-2 max-w-md text-sm leading-relaxed">
                        منصة للتوجيه الأكاديمي والمهني لطلاب الثانوية في اليمن
                    </p>
                </div>

                <nav aria-label="روابط التذييل" class="site-footer-nav">
                    <ul class="flex flex-wrap items-center gap-1 text-sm font-semibold">
                        <li>
                            <a href="{{ url('/') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-lg px-3">الرئيسية</a>
                        </li>
                        <li>
                            <a href="{{ route('specializations.index') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-lg px-3">التخصصات</a>
                        </li>
                        @auth
                            @if (auth()->user()->role === 'student')
                                <li>
                                    <a href="{{ route('assessment.intro') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-lg px-3">استكشاف ميولك</a>
                                </li>
                            @endif
                            @if (auth()->user()->role === 'admin')
                                <li>
                                    <a href="{{ route('admin.assessment-versions.index') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-lg px-3">إدارة التقييم</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.statistics.index') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-lg px-3">الإحصائيات</a>
                                </li>
                            @endif
                        @endauth
                        @guest
                            <li>
                                <a href="{{ route('login') }}" class="site-footer-link inline-flex min-h-10 items-center rounded-lg px-3">تسجيل الدخول</a>
                            </li>
                            <li>
                                <a href="{{ route('register') }}" class="site-footer-link site-footer-link-cta inline-flex min-h-10 items-center rounded-lg px-3">إنشاء حساب</a>
                            </li>
                        @endguest
                    </ul>
                </nav>
            </div>
        </div>
    </footer>
</body>
</html>
