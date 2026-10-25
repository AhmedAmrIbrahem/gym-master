
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#111827" id="gym-theme-color">
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
                document.querySelector('#gym-theme-color')?.setAttribute('content', document.documentElement.dataset.theme === 'light' ? '#f4f7fb' : '#111827');
            } catch (error) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- مكتبة SweetAlert2 للإشعارات الجانبية -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        html[data-theme="light"] { background-color: #f4f7fb !important; color-scheme: light; }
        html[data-theme="dark"] { background-color: #111827; color-scheme: dark; }
        body { min-height: 100dvh; }
        @supports (padding: env(safe-area-inset-bottom)) {
            body { padding-bottom: env(safe-area-inset-bottom); }
            html[data-theme="light"] body { background-color: #f4f7fb !important; }
        }
        body { font-family: 'Cairo', sans-serif; }

        /* ==========================================
           GYM MASTER — Mobile-First Header
           ========================================== */
        @media (max-width: 767px) {
            .gym-mobile-header {
                display: grid !important;
                grid-template-columns: 46px minmax(0, 1fr) 46px;
                align-items: center;
                direction: rtl;
                gap: 8px;
                width: 100%;
                min-width: 0;
                min-height: 72px;
                padding: 10px 14px;
                background: #1b2638 !important;
                border-bottom: 1px solid #34445d !important;
            }

            .gym-mobile-header .mobile-menu-button {
                grid-column: 1;
                justify-self: start;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 46px;
                height: 46px;
                min-width: 46px;
                min-height: 46px;
                background: #24344d;
                border: 1px solid #40536f;
                border-radius: 14px;
                color: #dbeafe;
                box-shadow: none;
            }

            .gym-mobile-brand {
                grid-column: 2;
                justify-self: center;
                display: inline-flex;
                direction: ltr;
                align-items: center;
                justify-content: center;
                min-width: 0;
                max-width: 100%;
                gap: 8px;
                color: #f1f5f9 !important;
                font-weight: 900;
                text-decoration: none;
                white-space: nowrap;
            }

            .gym-mobile-brand-mark {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 34px;
                width: 34px;
                height: 34px;
                border-radius: 11px;
                background: linear-gradient(145deg, #2563eb, #4f46e5);
                border: 1px solid rgba(191, 219, 254, .18);
                box-shadow: 0 4px 12px rgba(37, 99, 235, .22);
                color: #fff;
            }

            .gym-mobile-brand-mark svg {
                width: 19px;
                height: 19px;
            }

            .gym-mobile-brand-label {
                display: inline-block;
                overflow: hidden;
                text-overflow: ellipsis;
                font-family: 'Cairo', sans-serif;
                font-size: clamp(13px, 4vw, 16px);
                font-weight: 900;
                letter-spacing: .2px;
                line-height: 1.3;
            }

            .gym-mobile-header-theme {
                grid-column: 3;
                justify-self: end;
                display: flex;
            }

            .gym-mobile-header .theme-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 46px !important;
                height: 46px !important;
                min-width: 46px !important;
                min-height: 46px !important;
                padding: 0 !important;
                gap: 0 !important;
                border-radius: 14px !important;
                border: 1px solid #40536f !important;
                background: #24344d !important;
                color: #e0e7ff !important;
                box-shadow: none !important;
                transform: none !important;
            }

            .gym-mobile-header .theme-toggle .theme-switch-track,
            .gym-mobile-header .theme-toggle .theme-label {
                display: none !important;
            }

            .gym-mobile-header .theme-icon-sun,
            .gym-mobile-header .theme-icon-moon {
                width: 22px;
                height: 22px;
            }

            html[data-theme="dark"] .gym-mobile-header .theme-icon-sun {
                display: block !important;
            }

            html[data-theme="dark"] .gym-mobile-header .theme-icon-moon {
                display: none !important;
            }

            html[data-theme="light"] .gym-mobile-header .theme-icon-moon {
                display: block !important;
            }

            html[data-theme="light"] .gym-mobile-header .theme-icon-sun {
                display: none !important;
            }

            .gym-mobile-header .mobile-menu-button:focus-visible,
            .gym-mobile-header .theme-toggle:focus-visible,
            .gym-mobile-brand:focus-visible {
                outline: 3px solid #60a5fa;
                outline-offset: 2px;
            }

            /* عنوان الصفحة تحت الهيدر */
            .gym-mobile-page-header {
                min-width: 0;
                padding: 14px 16px !important;
                background: #202c3f !important;
                border-bottom: 1px solid #34445d !important;
            }

            .gym-mobile-page-header h1,
            .gym-mobile-page-header h2 {
                font-size: clamp(1.05rem, 5vw, 1.42rem) !important;
                line-height: 1.6;
                white-space: normal;
                overflow-wrap: break-word;
            }

            /* Light Mode */
            html[data-theme="light"] .gym-mobile-header {
                background: #fff !important;
                border-bottom-color: #dbe5f1 !important;
            }

            html[data-theme="light"] .gym-mobile-brand {
                color: #1e293b !important;
            }

            html[data-theme="light"] .gym-mobile-header .mobile-menu-button,
            html[data-theme="light"] .gym-mobile-header .theme-toggle {
                background: #f1f5fb !important;
                border-color: #d3dfed !important;
                color: #1d4ed8 !important;
            }

            html[data-theme="light"] .gym-mobile-page-header {
                background: #f8fafc !important;
                border-bottom-color: #dbe5f1 !important;
            }
        }

        @media (max-width: 359px) {
            .gym-mobile-header {
                padding-inline: 9px;
                gap: 5px;
            }

            .gym-mobile-brand {
                gap: 5px;
            }

            .gym-mobile-brand-mark {
                width: 30px;
                height: 30px;
                flex-basis: 30px;
            }

            .gym-mobile-brand-label {
                font-size: 12px;
            }
        }

        /* --- تنسيق شات الكوتش الذكي --- */
        .chat-body,
        #chat-messages,
        .chat-messages-container {
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const color = document.getElementById('gym-theme-color');
            const update = () => color?.setAttribute(
                'content',
                document.documentElement.dataset.theme === 'light'
                    ? '#f4f7fb'
                    : '#111827'
            );

            new MutationObserver(update).observe(
                document.documentElement,
                {
                    attributes: true,
                    attributeFilter: ['data-theme']
                }
            );

            update();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>

<body class="bg-gray-900 text-gray-100 antialiased min-h-screen">

<!-- إشعارات النجاح -->
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

<!-- إشعارات الخطأ -->
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

<!-- غلاف الصفحة الرئيسي -->
<div class="flex min-h-screen w-full">

    <div
        class="mobile-nav-backdrop"
        data-mobile-nav-backdrop
        hidden
    ></div>

    <!-- Sidebar -->
    <aside
        id="gym-sidebar"
        class="gym-sidebar w-64 bg-gray-800 border-l border-gray-700 flex flex-col justify-between md:flex min-h-screen sticky top-0 shrink-0"
        aria-label="القائمة الرئيسية"
    >
        <div>

            <!-- إغلاق القائمة على الموبايل -->
            <div class="md:hidden flex justify-between items-center px-4 pt-4 pb-1 mobile-nav-top">
                <span class="text-xs font-bold text-gray-400">
                    القائمة الرئيسية
                </span>

                <button
                    type="button"
                    data-mobile-nav-close
                    class="mobile-nav-close"
                    aria-label="إغلاق القائمة"
                    title="إغلاق القائمة"
                >
                    <svg
                        aria-hidden="true"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    >
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b border-gray-700 px-6">
                <a
                    href="{{ Auth::user()->isSuperAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                    aria-label="الرجوع للرئيسية"
                    title="الرجوع للرئيسية"
                    class="text-xl font-extrabold text-blue-500 flex items-center gap-2 rounded-lg hover:opacity-80 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                >
                    <span>🏋️‍♂️</span>
                    GYM DASHBOARD
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-2">

                @if(Auth::user()->isSuperAdmin())

                    <!-- السوبر أدمن -->
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white shadow-lg' : 'text-purple-400 hover:bg-purple-900/30 hover:text-purple-200' }}"
                    >
                        <span>📊</span>
                        الإحصائيات العامة
                    </a>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-purple-600 text-white shadow-lg' : 'text-purple-400 hover:bg-purple-900/30 hover:text-purple-200' }}"
                    >
                        <span>👥</span>
                        إدارة المشتركين
                    </a>

                @else

                    <!-- المشتركين -->
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    >
                        <span>📊</span>
                        الرئيسية والتمرين
                    </a>

                    <a
                        href="{{ route('exercises.index') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('exercises.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    >
                        <span>💪</span>
                        بنك التمارين
                    </a>

                    <a
                        href="{{ route('programs.index') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('programs.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    >
                        <span>📅</span>
                        إدارة الأنظمة (PPL / Upper..)
                    </a>

                    <a
                        href="{{ route('history.index') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('history.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    >
                        <span>📜</span>
                        سجل التمارين والتاريخ
                    </a>

                    <a
                        href="{{ route('measurements.index') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('measurements.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    >
                        <span>⚖️</span>
                        وزن الجسم والقياسات
                    </a>

                    <a
                        href="{{ route('nutrition.index') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('nutrition.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    >
                        <span>🥗</span>
                        تتبع السعرات والتغذية
                    </a>

                @endif

            </nav>
        </div>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-gray-700">
            <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-gray-900/60 border border-gray-700/80 hover:border-blue-500/50 transition group">

                <!-- بيانات الحساب -->
                <a
                    href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 overflow-hidden flex-1 min-w-0"
                    title="تعديل الملف الشخصي"
                >
                    <div class="w-10 h-10 rounded-xl overflow-hidden border border-blue-500 shrink-0 shadow-md group-hover:scale-105 transition">
                        <img
                            src="{{ Auth::user()->avatar_url }}"
                            alt="{{ Auth::user()->name }}"
                            class="w-full h-full object-cover"
                        >
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

                <!-- تسجيل الخروج -->
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="shrink-0"
                >
                    @csrf

                    <button
                        type="submit"
                        title="تسجيل الخروج"
                        class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition"
                    >
                        🚪
                    </button>
                </form>

            </div>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen min-w-0">

        <!-- ======================================
             Mobile Header
             ====================================== -->
        <header
            class="bg-gray-800 border-b border-gray-700 md:hidden mobile-topbar gym-mobile-header"
            aria-label="الشريط العلوي"
        >

            <!-- زر القائمة -->
            <button
                type="button"
                data-mobile-nav-open
                aria-controls="gym-sidebar"
                aria-expanded="false"
                class="mobile-menu-button"
                aria-label="فتح القائمة"
                title="القائمة الرئيسية"
            >
                <svg
                    aria-hidden="true"
                    width="23"
                    height="23"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.9"
                    stroke-linecap="round"
                >
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>

            <!-- اسم النظام -->
            <a
                href="{{ Auth::user()->isSuperAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                class="gym-mobile-brand"
                aria-label="Gym Master — الرجوع للرئيسية"
            >
                <span class="gym-mobile-brand-mark" aria-hidden="true">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="m6.5 6.5 11 11M4.3 9l4.7-4.7M15 19.7l4.7-4.7M2.6 7.3l4.7-4.7M16.7 21.4l4.7-4.7"/>
                    </svg>
                </span>

                <span class="gym-mobile-brand-label">
                    GYM MASTER
                </span>
            </a>

            <!-- زر الوضع الداكن والفاتح -->
            <div class="gym-mobile-header-theme">
                <x-theme-toggle />
            </div>

        </header>

        <!-- Page Header -->
        @if (isset($header))
            <header class="bg-gray-800 border-b border-gray-700 py-3 px-4 sm:px-8 flex items-center justify-between gap-3 gym-mobile-page-header">

                <div class="min-w-0 flex-1">
                    {{ $header }}
                </div>

                <div class="hidden md:flex shrink-0 items-center">
                    <x-theme-toggle />
                </div>

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

<!-- مؤشر تحميل الصفحات -->
<div
    id="gym-page-loader"
    class="gym-page-loader"
    role="status"
    aria-live="polite"
    aria-label="جاري التحميل"
    hidden
>
    <div class="gym-loader-panel">
        <span class="gym-loader-spinner" aria-hidden="true"></span>
        <span>جاري التحميل...</span>
    </div>
</div>

<!-- Chatbot & Reminders -->
<x-chatbot-widget />
<x-gentle-reminders />

@include('components.password-visibility')

<!-- Mobile Sidebar -->
<script>
    (function () {
        const sidebar = document.getElementById('gym-sidebar');
        const open = document.querySelector('[data-mobile-nav-open]');
        const close = document.querySelector('[data-mobile-nav-close]');
        const backdrop = document.querySelector('[data-mobile-nav-backdrop]');

        if (!sidebar || !open || !backdrop) return;

        const isMobile = () =>
            window.matchMedia('(max-width: 767px)').matches;

        function setOpen(value) {
            const active = !!value && isMobile();

            sidebar.classList.toggle('mobile-nav-open', active);
            backdrop.hidden = !active;

            document.body.classList.toggle('mobile-nav-locked', active);

            open.setAttribute('aria-expanded', String(active));

            if (active) {
                close?.focus();
            } else if (value === false && isMobile()) {
                open.focus();
            }
        }

        open.addEventListener('click', () => setOpen(true));

        close?.addEventListener('click', () => setOpen(false));

        backdrop.addEventListener('click', () => setOpen(false));

        sidebar.querySelectorAll('nav a').forEach(a => {
            a.addEventListener('click', () => setOpen(false));
        });

        document.addEventListener('keydown', e => {
            if (
                e.key === 'Escape' &&
                sidebar.classList.contains('mobile-nav-open')
            ) {
                setOpen(false);
            }
        });

        window.addEventListener('resize', () => {
            if (!isMobile()) {
                setOpen(false);
            }
        });
    })();
</script>

<!-- Page Loader -->
<script>
    (function () {
        const loader = document.getElementById('gym-page-loader');

        if (!loader) return;

        let timer;

        function showSoon() {
            clearTimeout(timer);

            timer = setTimeout(() => {
                loader.hidden = false;
            }, 180);
        }

        function reset() {
            clearTimeout(timer);
            loader.hidden = true;
        }

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');

            if (
                !link ||
                event.defaultPrevented ||
                event.button !== 0 ||
                event.metaKey ||
                event.ctrlKey ||
                event.shiftKey ||
                event.altKey
            ) return;

            if (
                link.hasAttribute('download') ||
                (link.target && link.target !== '_self')
            ) return;

            const url = new URL(link.href, window.location.href);

            if (
                url.origin !== window.location.origin ||
                !['http:', 'https:'].includes(url.protocol)
            ) return;

            if (
                url.pathname === location.pathname &&
                url.search === location.search &&
                url.hash
            ) return;

            showSoon();
        });

        document.addEventListener('submit', event => {
            const form = event.target;

            if (
                !(form instanceof HTMLFormElement) ||
                event.defaultPrevented
            ) return;

            if (form.target && form.target !== '_self') return;

            if (!form.checkValidity()) return;

            showSoon();
        });

        window.addEventListener('pageshow', reset);
        window.addEventListener('pagehide', reset);
    })();
</script>

<!-- Visible viewport measurements for dialogs and search panels while phone keyboard is open. -->
<script>
(function () {
    const viewport = window.visualViewport;
    const update = function () {
        const visibleHeight = Math.round(viewport ? viewport.height : window.innerHeight);
        const viewportOffset = viewport ? viewport.offsetTop : 0;
        const keyboardHeight = Math.max(0, Math.round(window.innerHeight - visibleHeight - viewportOffset));
        document.documentElement.style.setProperty('--gym-visual-viewport-height', visibleHeight + 'px');
        document.documentElement.style.setProperty('--gym-keyboard-inset', keyboardHeight + 'px');
    };
    viewport?.addEventListener('resize', update, { passive: true });
    viewport?.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
})();
</script>

<!-- Searchable Selects -->
<x-searchable-selects />

</body>
</html>
