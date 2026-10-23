<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gym Master') }}</title>

    <!-- Fonts & Vite -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <script>
        (function () {
            try {
                document.documentElement.dataset.theme = localStorage.getItem('gym-theme') === 'light' ? 'light' : 'dark';
            } catch (error) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- مكتبة SweetAlert2 للإشعارات الجانبية -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Cairo', sans-serif; }

        /* --- تنسيق شات الكوتش الذكي --- */
        .chat-body, #chat-messages, .chat-messages-container {
            max-height: 380px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
            scroll-behavior: smooth;
        }

        .chat-body::-webkit-scrollbar,
        #chat-messages::-webkit-scrollbar,
        .chat-messages-container::-webkit-scrollbar {
            width: 6px;
        }

        .chat-body::-webkit-scrollbar-thumb,
        #chat-messages::-webkit-scrollbar-thumb,
        .chat-messages-container::-webkit-scrollbar-thumb {
            background-color: #374151;
            border-radius: 4px;
        }

        .chat-message-bot {
            background-color: #1f2937;
            color: #f3f4f6;
            padding: 12px 16px;
            border-radius: 12px 12px 0px 12px;
            white-space: pre-line;
            line-height: 1.7;
            font-size: 14px;
            direction: rtl;
            text-align: right;
            word-break: break-word;
            border: 1px solid #374151;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 antialiased min-h-screen">

<!-- سكريبت إظهار إشعارات التوست الجانبية الصغيرة والأنيقة في أي صفحة بالسيستم -->
@if(session('success'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '{{ session("success") }}',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: '#1f2937',
            color: '#fff',
            customClass: {
                popup: 'rounded-xl border border-emerald-500/30 shadow-2xl p-3 text-xs',
                title: 'text-xs font-bold text-white mr-2',
                icon: 'scale-75'
            }
        });
    </script>
@endif

@if(session('error') || $errors->any())
    <script>
        @php
            $errorMsg = session('error') ?? $errors->first();
            if(str_contains($errorMsg, 'already been taken')) {
                if(str_contains($errorMsg, 'email')) {
                    $errorMsg = 'عذراً، البريد الإلكتروني مسجل مسبقاً!';
                } elseif(str_contains($errorMsg, 'phone')) {
                    $errorMsg = 'عذراً، رقم الهاتف مسجل مسبقاً!';
                } else {
                    $errorMsg = 'هذه البيانات مسجلة مسبقاً.';
                }
            }
        @endphp

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: '{{ $errorMsg }}',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            background: '#1f2937',
            color: '#fff',
            customClass: {
                popup: 'rounded-xl border border-red-500/40 shadow-2xl p-3 text-xs',
                title: 'text-xs font-bold text-white mr-2',
                icon: 'scale-75'
            }
        });
    </script>
@endif

<!-- غلاف الصفحة الرئيسي بالـ Flex -->
<div class="flex min-h-screen w-full">
    <div class="mobile-nav-backdrop" data-mobile-nav-backdrop hidden></div>

    <!-- Sidebar القائمة الجانبية -->
    <aside id="gym-sidebar" class="gym-sidebar w-64 bg-gray-800 border-l border-gray-700 flex flex-col justify-between md:flex min-h-screen sticky top-0 shrink-0" aria-label="القائمة الرئيسية">
        <div>
            <div class="md:hidden flex justify-between items-center px-4 pt-4 pb-1 mobile-nav-top">
                <span class="text-xs font-bold text-gray-400">القائمة الرئيسية</span>
                <button type="button" data-mobile-nav-close class="mobile-nav-close" aria-label="إغلاق القائمة" title="إغلاق القائمة">
                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b border-gray-700 px-6">
                <h1 class="text-xl font-extrabold text-blue-500 flex items-center gap-2">
                    <span>🏋️‍♂️</span> GYM DASHBOARD
                </h1>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-2">
                @if(Auth::user()->isSuperAdmin())
                    <!-- القائمة الخاصة بالسوبر أدمن -->
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white shadow-lg' : 'text-purple-400 hover:bg-purple-900/30 hover:text-purple-200' }}">
                        <span>📊</span> الإحصائيات العامة
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-purple-600 text-white shadow-lg' : 'text-purple-400 hover:bg-purple-900/30 hover:text-purple-200' }}">
                        <span>👥</span> إدارة المشتركين
                    </a>
                @else
                    <!-- القائمة الخاصة بالمشتركين العاديين -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                        <span>📊</span> الرئيسية والتمرين
                    </a>

                    <a href="{{ route('exercises.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('exercises.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                        <span>💪</span> بنك التمارين
                    </a>

                    <a href="{{ route('programs.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('programs.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                        <span>📅</span> إدارة الأنظمة (PPL / Upper..)
                    </a>

                    <a href="{{ route('history.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('history.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                        <span>📜</span> سجل التمارين والتاريخ
                    </a>

                    <a href="{{ route('measurements.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('measurements.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                        <span>⚖️</span> وزن الجسم والقياسات
                    </a>

                    <a href="{{ route('nutrition.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('nutrition.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                        <span>🥗</span> تتبع السعرات والتغذية
                    </a>
                @endif
            </nav>
        </div>

        <!-- User profile & Logout -->
        <div class="p-4 border-t border-gray-700">
            <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-gray-900/60 border border-gray-700/80 hover:border-blue-500/50 transition group">

                <!-- رابط الملف الشخصي والصورة المرفوعة -->
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 overflow-hidden flex-1 min-w-0" title="تعديل الملف الشخصي">
                    <div class="w-10 h-10 rounded-xl overflow-hidden border border-blue-500 shrink-0 shadow-md group-hover:scale-105 transition">
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                    </div>

                    <div class="truncate">
                        <p class="text-xs font-bold text-white truncate group-hover:text-blue-400 transition">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-[10px] text-gray-400 truncate">
                            {{ Auth::user()->email }}
                        </p>
                    </div>
                </a>

                <!-- زر تسجيل الخروج -->
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="تسجيل الخروج" class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition">
                        🚪
                    </button>
                </form>

            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen min-w-0">
        <!-- Top Mobile Header -->
        <header class="bg-gray-800 border-b border-gray-700 p-3 md:hidden flex justify-between items-center gap-2 mobile-topbar">
            <div class="flex items-center gap-2 min-w-0">
                <button type="button" data-mobile-nav-open aria-controls="gym-sidebar" aria-expanded="false" class="mobile-menu-button" aria-label="فتح القائمة">
                    <svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-base font-bold text-blue-500 truncate">🏋️‍♂️ Gym Master</h1>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-theme-toggle />
                @if(Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.users.index') }}" class="text-xs bg-purple-600 text-white px-2.5 py-1 rounded-lg font-bold">المشتركين</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="w-8 h-8 rounded-lg overflow-hidden border border-blue-500">
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-red-400">خروج</button>
                </form>
            </div>
        </header>

        <!-- Page Header -->
        @if (isset($header))
            <header class="bg-gray-800 border-b border-gray-700 py-3 px-4 sm:px-8 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">{{ $header }}</div>
                <div class="hidden md:flex shrink-0 items-center"><x-theme-toggle /></div>
            </header>
        @else
            <header class="hidden md:flex bg-gray-800 border-b border-gray-700 px-6 py-3 justify-end items-center">
                <x-theme-toggle />
            </header>
        @endif

        <!-- Main Body -->
        <main class="p-3 sm:p-6 lg:p-8 flex-1 min-w-0 mobile-content">
            {{ $slot }}
        </main>
    </div>

</div>

<!-- مؤشر التنقل يظهر عند الانتقال لصفحة أخرى أو إرسال نموذج عادي -->
<div id="gym-page-loader" class="gym-page-loader" role="status" aria-live="polite" aria-label="جاري التحميل" hidden>
    <div class="gym-loader-panel">
        <span class="gym-loader-spinner" aria-hidden="true"></span>
        <span>جاري التحميل...</span>
    </div>
</div>

<!-- الشات بوت الذكي مستدعى خارج غلاف الـ Flex مباشرة ليكون عائماً بشكل حر أسفل الشمال -->
<x-chatbot-widget />

@include('components.password-visibility')
<script>
(function () {
    const sidebar = document.getElementById('gym-sidebar');
    const open = document.querySelector('[data-mobile-nav-open]');
    const close = document.querySelector('[data-mobile-nav-close]');
    const backdrop = document.querySelector('[data-mobile-nav-backdrop]');
    if (!sidebar || !open || !backdrop) return;
    const isMobile = () => window.matchMedia('(max-width: 767px)').matches;
    function setOpen(value) {
        const active = !!value && isMobile();
        sidebar.classList.toggle('mobile-nav-open', active);
        backdrop.hidden = !active;
        document.body.classList.toggle('mobile-nav-locked', active);
        open.setAttribute('aria-expanded', String(active));
        if (active) close?.focus(); else if (value === false && isMobile()) open.focus();
    }
    open.addEventListener('click', () => setOpen(true));
    close?.addEventListener('click', () => setOpen(false));
    backdrop.addEventListener('click', () => setOpen(false));
    sidebar.querySelectorAll('nav a').forEach(a => a.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && sidebar.classList.contains('mobile-nav-open')) setOpen(false); });
    window.addEventListener('resize', () => { if (!isMobile()) setOpen(false); });
})();
</script>
<script>
(function () {
    const loader = document.getElementById('gym-page-loader');
    if (!loader) return;
    let timer;
    function showSoon() {
        clearTimeout(timer);
        timer = setTimeout(() => { loader.hidden = false; }, 180);
    }
    function reset() { clearTimeout(timer); loader.hidden = true; }
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin || !['http:', 'https:'].includes(url.protocol)) return;
        if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
        showSoon();
    });
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;
        if (form.target && form.target !== '_self') return;
        if (!form.checkValidity()) return;
        showSoon();
    });
    window.addEventListener('pageshow', reset);
    window.addEventListener('pagehide', reset);
})();
</script>
</body>
</html>
