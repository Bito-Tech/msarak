<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <title>@hasSection('title')@yield('title') | @endifمسارك</title>

    {{-- الخط العربي المعتمد: IBM Plex Sans Arabic ذو الرصانة التقنية والوضوح العالي في الواجهات البرمجية --}}
    <link rel="preload" as="font" type="font/woff2" href="/fonts/ibm-plex-sans-arabic-arabic-400-normal.woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased font-sans">
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

    <main id="main" tabindex="-1" class="container-page flex-1 break-words py-8 lg:py-12">
        <x-ui.flash class="mb-6" />
        @yield('content')
    </main>

    <footer class="mt-auto">
        <div class="bg-brand-900 text-white">
            <div class="container-page flex flex-wrap items-center justify-between gap-x-8 gap-y-6 py-8">
                <div class="max-w-md">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 text-sm font-extrabold text-white ring-1 ring-inset ring-white/25" aria-hidden="true">م</span>
                        <span class="text-xl font-bold">مسارك</span>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-brand-100/90">
                        منصة للتوجيه الأكاديمي والمهني لطلاب الثانوية في اليمن
                    </p>
                </div>

                <nav aria-label="روابط التذييل">
                    <ul class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium">
                        <li>
                            <a href="{{ url('/') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-brand-100/90 transition-colors hover:bg-white/10 hover:text-white">الرئيسية</a>
                        </li>
                        <li>
                            <a href="{{ route('specializations.index') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-brand-100/90 transition-colors hover:bg-white/10 hover:text-white">التخصصات</a>
                        </li>
                        @auth
                            @if (auth()->user()->role === 'student')
                                <li>
                                    <a href="{{ route('assessment.intro') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-brand-100/90 transition-colors hover:bg-white/10 hover:text-white">استكشاف ميولك</a>
                                </li>
                            @endif
                            @if (auth()->user()->role === 'admin')
                                <li>
                                    <a href="{{ route('admin.assessment-versions.index') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-brand-100/90 transition-colors hover:bg-white/10 hover:text-white">إدارة التقييم</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.statistics.index') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-brand-100/90 transition-colors hover:bg-white/10 hover:text-white">الإحصائيات</a>
                                </li>
                            @endif
                        @endauth
                        @guest
                            <li>
                                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-brand-100/90 transition-colors hover:bg-white/10 hover:text-white">تسجيل الدخول</a>
                            </li>
                            <li>
                                <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-brand-100/90 transition-colors hover:bg-white/10 hover:text-white">إنشاء حساب</a>
                            </li>
                        @endguest
                    </ul>
                </nav>
            </div>
        </div>
    </footer>
</body>
</html>
