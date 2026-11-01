
<style>
    [x-cloak] {
        display: none !important;
    }

    .gym-chat-window {
        position: fixed !important;
        bottom: 85px !important;
        left: 50% !important;
        right: auto !important;
        transform: translateX(-50%);
        width: min(900px, calc(100vw - 32px));
        height: min(750px, calc(100dvh - 105px));
        z-index: 100000 !important;
    }

    .gym-chat-window.fullscreen {
        top: var(--gym-chat-vtop, 0px) !important;
        bottom: auto !important;
        left: 0 !important;
        right: 0 !important;
        transform: none !important;
        width: 100vw !important;
        height: var(--gym-chat-vh, 100dvh) !important;
    }

    .gym-chat-messages {
        scrollbar-width: thin;
        scrollbar-color: #374151 transparent;
        min-height: 0;
        overscroll-behavior: contain;
    }

    .gym-chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    .gym-chat-messages::-webkit-scrollbar-thumb {
        background: #374151;
        border-radius: 10px;
    }

    .gym-chat-text {
        overflow-wrap: anywhere;
        word-break: break-word;
        white-space: pre-wrap;
    }

    .gym-chat-photo { display: block; width: auto; max-width: 100%; max-height: 220px; object-fit: contain; border-radius: 12px; margin-bottom: 10px; }
    .gym-chat-attachment { display: flex; align-items: center; gap: 12px; margin: 0 auto 10px; max-width: 768px; padding: 10px; border: 1px solid #64748b; border-radius: 12px; }
    .gym-chat-attachment img { width: 64px; height: 64px; object-fit: cover; border-radius: 8px; }
    .gym-chat-upload { flex-shrink: 0; min-width: 44px; min-height: 48px; border-radius: 12px; border: 1px solid #64748b; padding: 8px; color: #93c5fd; }
    html[data-theme="light"] .gym-chat-upload { color: #1d4ed8; }
    .gym-chat-photo-note { color: #94a3b8; font-size: 11px; line-height: 1.7; }
    .gym-meal-review { width: min(100%, 640px); min-width: 0; margin-top: 16px; border-top: 1px solid #64748b; padding-top: 14px; }
    .gym-meal-review-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .gym-meal-review label { display: block; min-width: 0; font-size: 12px; line-height: 1.7; }
    .gym-meal-review input, .gym-meal-review select { display: block; width: 100%; min-width: 0; max-width: 100%; box-sizing: border-box; padding: 10px; min-height: 44px; margin-top: 5px; border: 1px solid #64748b; border-radius: 10px; background: #111827; color: #f1f5f9; font-size: 16px; color-scheme: dark; }
    .gym-meal-review input[type="number"], .gym-meal-review input[type="date"] { direction: ltr; text-align: left; }
    .gym-meal-review-item { min-width: 0; padding: 12px; margin: 12px 0; border: 1px solid #64748b; border-radius: 12px; }
    .gym-meal-review-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
    .gym-meal-review-actions button, .gym-meal-review-actions a { min-height: 44px; padding: 10px 14px; border-radius: 10px; font-size: 13px; }
    .gym-meal-review-save { background: #2563eb; color: white; }
    .gym-meal-review-summary { padding: 12px; background: rgba(59,130,246,.12); border-radius: 10px; font-size: 13px; line-height: 1.9; }
    .gym-meal-review-error { color: #fca5a5; font-size: 13px; line-height: 1.8; margin-top: 10px; }
    html[data-theme="light"] .gym-meal-review input, html[data-theme="light"] .gym-meal-review select { background: #fff; color: #172033; color-scheme: light; border-color: #b8c6d8; }
    html[data-theme="light"] .gym-meal-review-error { color: #b91c1c; }
    @media (min-width: 768px) { .gym-meal-review-nutrients { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (max-width: 360px) { .gym-meal-review-grid { grid-template-columns: minmax(0, 1fr); } }
    html[data-theme="light"] .gym-chat-photo-note { color: #52647c; }

    /* حقل كتابة الشات: بدون أسهم أو شريط تمرير ظاهر */
    .gym-chat-input {
        resize: none !important;
        overflow-x: hidden !important;
        overflow-y: auto;
        scrollbar-width: none !important;
        -ms-overflow-style: none;
        appearance: none;
        -webkit-appearance: none;
        box-sizing: border-box;
        line-height: 1.6;
    }

    .gym-chat-input::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }


    .gym-calorie-card { direction: rtl; width: min(100%, 580px); color: #e5e7eb; }
    .gym-calorie-hero { text-align: center; background: #152d4d; border: 1px solid #315c8d; border-radius: 16px; padding: 18px 12px; margin: 12px 0; }
    .gym-calorie-hero strong { display: block; font-size: clamp(28px, 5vw, 38px); color: #93c5fd; line-height: 1.35; }
    .gym-calorie-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin: 12px 0; }
    .gym-calorie-tile { background: #111c2e; border: 1px solid #374151; border-radius: 12px; padding: 12px; }
    .gym-calorie-tile strong { display: block; font-size: 22px; color: #f9fafb; margin-top: 6px; }
    .gym-calorie-muted { font-size: 12px; color: #aab9cf; }
    .gym-calorie-macros { background: #111c2e; border: 1px solid #374151; border-radius: 14px; padding: 14px; }
    .gym-calorie-row { display: flex; justify-content: space-between; gap: 10px; margin: 12px 0 5px; font-size: 13px; }
    .gym-calorie-track { height: 8px; background: #374151; border-radius: 100px; overflow: hidden; }
    .gym-calorie-fill { height: 100%; border-radius: 100px; }

    /* Formatting generated locally from escaped text (never raw model HTML) */
    .gym-chat-rich { font-size: 14px; line-height: 1.95; overflow-wrap: anywhere; }
    .gym-chat-rich p { margin: 0 0 10px; }
    .gym-chat-rich p:last-child { margin-bottom: 0; }
    .gym-chat-rich h3 { color: #93c5fd; font-weight: 700; font-size: 16px; margin: 14px 0 6px; }
    .gym-chat-rich h4 { color: #bfdbfe; font-weight: 700; font-size: 14px; margin: 10px 0 5px; }
    .gym-chat-rich ul, .gym-chat-rich ol { padding-right: 23px; margin: 8px 0 13px; }
    .gym-chat-rich ul { list-style: disc outside; }
    .gym-chat-rich ol { list-style: decimal outside; }
    .gym-chat-rich li { margin: 5px 0; }
    .gym-chat-rich strong { color: #f8fafc; font-weight: 700; }
    .gym-chat-rich hr { border: 0; border-top: 1px solid #374151; margin: 12px 0; }
    .gym-chat-rich code { direction: ltr; unicode-bidi: isolate; background: #111827; padding: 1px 5px; border-radius: 5px; }
    .gym-chat-action { background: #111827; border: 1px solid #374151; color: #cbd5e1; border-radius: 9px; padding: 5px 10px; font-size: 12px; }
    .gym-chat-action:hover { color: white; border-color: #60a5fa; }
    .gym-chat-quick { background: #172338; color: #bfdbfe; border: 1px solid #334155; border-radius: 999px; padding: 8px 12px; font-size: 12px; }
    .gym-chat-quick:hover { background: #1d4ed8; color: white; }

    /* Light mode: chatbot is teleported to body, so theme selectors must start at html. */
    html[data-theme="light"] .gym-chat-panel { background: #f8fafc !important; border-color: #d8e2ee !important; }
    html[data-theme="light"] .gym-chat-messages { background: #f0f5fb !important; scrollbar-color: #bdcbe0 transparent; }
    html[data-theme="light"] .gym-chat-messages::-webkit-scrollbar-thumb { background: #bdcbe0; }
    html[data-theme="light"] .gym-chat-messages > div > .flex.justify-start > .bg-gray-800\/90,
    html[data-theme="light"] .gym-chat-messages .bg-gray-800 { background: #fff !important; color: #25364c !important; border-color: #d8e2ee !important; }
    html[data-theme="light"] .gym-chat-messages .text-gray-200,
    html[data-theme="light"] .gym-chat-messages .text-gray-300 { color: #52647c !important; }
    html[data-theme="light"] .gym-chat-messages .text-blue-400 { color: #1d4ed8 !important; }
    html[data-theme="light"] .gym-chat-quick { background: #fff !important; color: #1e40af !important; border: 1px solid #bdd1ef !important; box-shadow: 0 1px 3px rgba(15,23,42,.06); }
    html[data-theme="light"] .gym-chat-quick:hover,
    html[data-theme="light"] .gym-chat-quick:focus-visible { background: #e9f1ff !important; color: #1d4ed8 !important; border-color: #2563eb !important; }
    html[data-theme="light"] .gym-chat-rich h3 { color: #1d4ed8; }
    html[data-theme="light"] .gym-chat-rich h4 { color: #1e40af; }
    html[data-theme="light"] .gym-chat-rich strong { color: #172b45; }
    html[data-theme="light"] .gym-chat-rich hr { border-color: #d8e2ee; }
    html[data-theme="light"] .gym-chat-rich code { background: #e8eef7; color: #263b57; }
    html[data-theme="light"] .gym-chat-action { background: #f1f5fb; border-color: #cfdceb; color: #334b6b; }
    html[data-theme="light"] .gym-chat-action:hover { color: #1d4ed8; border-color: #2563eb; }
    html[data-theme="light"] .gym-chat-panel > div:last-child { background: #fff !important; border-top-color: #d8e2ee !important; }
    html[data-theme="light"] .gym-chat-input { background: #f8fafc !important; color: #1e293b !important; border-color: #cbd5e1 !important; }
    html[data-theme="light"] .gym-chat-input::placeholder { color: #64748b !important; }
    html[data-theme="light"] .gym-chat-input:focus { border-color: #2563eb !important; }
    html[data-theme="light"] .gym-chat-panel > div:last-child p.text-gray-500 { color: #64748b !important; }
    html[data-theme="light"] .gym-calorie-card { color: #263b57; }
    html[data-theme="light"] .gym-calorie-hero { background: #e8f2ff; border-color: #bfd7fa; }
    html[data-theme="light"] .gym-calorie-hero strong { color: #1d4ed8; }
    html[data-theme="light"] .gym-calorie-tile,
    html[data-theme="light"] .gym-calorie-macros { background: #f8fafc; border-color: #d8e2ee; }
    html[data-theme="light"] .gym-calorie-tile strong { color: #172b45; }
    html[data-theme="light"] .gym-calorie-muted { color: #64748b; }
    html[data-theme="light"] .gym-calorie-track { background: #e2e8f0; }

    /* iOS/Android: readable composer and an unobtrusive launcher. */
    @media (max-width: 767px) {
        .gym-chat-input { font-size: 16px !important; }
        .gym-chat-launcher { left: 12px !important; right: auto !important;
            bottom: calc(12px + env(safe-area-inset-bottom, 0px)) !important; }
        .gym-chat-launcher-button { width: 48px !important; height: 48px !important;
            min-width: 48px !important; min-height: 48px !important;
            box-shadow: 0 6px 20px rgba(15,23,42,.26) !important; }
        .gym-chat-launcher-button svg { width: 27px; height: 27px; }
        /* Leave scrolling room to reach the final action behind the fixed button. */
        .mobile-content { padding-bottom: max(92px, calc(76px + env(safe-area-inset-bottom, 0px))) !important; }
    }

    /* Theme-consistent surfaces: chat lives outside the page layout via teleport. */
    html[data-theme="light"] .gym-chat-header {
        background: linear-gradient(115deg, #e8f1ff 0%, #f8fbff 60%, #e8effd 100%) !important;
        color: #1e3a5f !important; border-bottom-color: #d5e3f5 !important;
    }
    html[data-theme="light"] .gym-chat-header h4 { color: #19365d !important; }
    html[data-theme="light"] .gym-chat-header .text-emerald-400 { color: #047857 !important; }
    html[data-theme="light"] .gym-chat-header .text-orange-400 { color: #9a3412 !important; }
    html[data-theme="light"] .gym-chat-header button { color: #315682 !important; border-color: #bfd2eb !important; }
    html[data-theme="light"] .gym-chat-header button:hover { background: #dceafe !important; color: #1d4ed8 !important; }
    html[data-theme="light"] .gym-chat-welcome > div { background: #fff !important; color: #334155 !important; border-color: #d6e2ef !important; }
    html[data-theme="light"] .gym-chat-bubble.bg-gray-800 { background: #fff !important; color: #25364c !important; border-color: #d6e2ef !important; }
    html[data-theme="light"] .gym-chat-bubble.bg-blue-600 { color: #fff !important; }
    html[data-theme="light"] .gym-chat-bubble .border-gray-700\/50 { border-color: #dce6f2 !important; }
    html[data-theme="light"] .gym-chat-composer { background: #fff !important; border-color: #d6e2ef !important; }
    html[data-theme="light"] .gym-chat-send { background: #2563eb !important; color: white !important; }
    html[data-theme="light"] .gym-chat-send:hover { background: #1d4ed8 !important; }
    .gym-chat-suggestions { align-items: stretch; }
    .gym-chat-quick { min-height: 37px; line-height: 1.4; text-align: center; }

    @media (max-width: 640px) {
        .gym-chat-window, .gym-chat-window.fullscreen {
            top: var(--gym-chat-vtop, 0px) !important;
            bottom: auto !important;
            left: 0 !important;
            right: 0 !important;
            transform: none !important;
            width: 100vw !important;
            height: var(--gym-chat-vh, 100dvh) !important;
        }

        .gym-chat-composer { padding-bottom: max(12px, env(safe-area-inset-bottom)) !important; }
        .gym-chat-composer .gym-chat-compose-form {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
        }
        .gym-chat-composer .gym-chat-input {
            grid-column: 1 / -1;
            grid-row: 1;
            width: 100%;
            min-height: 68px !important;
            padding: 12px 14px !important;
            font-size: 16px !important;
            line-height: 24px;
            border-radius: 14px;
        }
        .gym-chat-composer .gym-chat-upload {
            grid-column: 1;
            grid-row: 2;
            width: 48px;
            height: 44px;
            min-height: 44px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
        }
        .gym-chat-composer .gym-chat-send {
            grid-column: 2;
            grid-row: 2;
            justify-self: end;
            justify-content: center;
            min-width: 108px;
            min-height: 44px;
            padding: 10px 16px;
            border-radius: 12px;
        }
        .gym-chat-composer .gym-chat-attachment { gap: 10px; padding: 10px; }
        .gym-chat-composer .gym-chat-attachment img { flex-shrink: 0; width: 56px; height: 56px; }
        .gym-chat-composer .gym-chat-attachment .gym-chat-photo-note { flex: 1; min-width: 0; font-size: 12px; }
        .gym-chat-composer .gym-chat-attachment button { flex-shrink: 0; width: 40px; height: 40px; padding: 0; }
        .gym-chat-composer > p:last-child { font-size: 11px; line-height: 1.7; margin-top: 10px; }
        .gym-chat-header { padding: 11px 12px !important; gap: 7px; }
        .gym-chat-header .w-12.h-12 { width: 40px; height: 40px; }
        .gym-chat-header h4 { font-size: 14px; }
        .gym-chat-header .flex.items-center.gap-2.shrink-0 { gap: 4px; }
        .gym-chat-header button { width: 36px !important; height: 36px !important; min-width: 36px !important; }
        .gym-chat-suggestions { gap: 8px; }
        .gym-chat-quick { max-width: 100%; }
        .gym-chat-panel { border-radius: 0 !important; }
        .gym-chat-messages { padding: 12px !important; }
        .gym-chat-messages > div { width: 100%; min-width: 0; }
        .gym-chat-messages .gym-chat-rich,
        .gym-chat-messages .gym-chat-text { font-size: 13px; line-height: 1.85; overflow-wrap: anywhere; }
        .gym-chat-messages .gym-chat-rich * { max-width: 100%; }
        .gym-chat-messages .gym-chat-rich pre { overflow-x: auto; }
        .gym-chat-window .p-4 { padding: 12px; }
        .gym-chat-window header { min-width: 0; }
        .gym-calorie-grid { grid-template-columns: minmax(0, 1fr); }
    }
</style>

<!-- المكون الرئيسي للشات -->
<div
    x-data="chatbot()"
    class="gym-chat-launcher"
    style="
        position: fixed !important;
        bottom: 24px !important;
        left: 24px !important;
        right: auto !important;
        z-index: 99999 !important;
    "
>
    <!-- زر الشات العائم -->
    <button
        type="button"
        @click="open = !open; if(open) scrollToBottom()"
        :aria-label="open ? 'إغلاق الشات' : 'فتح الشات'"
        class="gym-chat-launcher-button bg-blue-600 hover:bg-blue-500
               text-white shadow-2xl
               flex items-center justify-center
               transition-all hover:scale-110
               active:scale-95
               border-2 border-blue-400/40
               cursor-pointer overflow-hidden"
        style="
            width: 58px;
            height: 58px;
            min-width: 58px;
            min-height: 58px;
            border-radius: 9999px !important;
            padding: 0 !important;
        "
    >
        <span
            x-show="!open"
            class="flex items-center justify-center w-full h-full p-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="text-white">
                <rect x="4" y="7" width="16" height="13" rx="4" />
                <path d="M12 3v4M9 3h6M4 12H2M22 12h-2M8 16h.01M16 16h.01M9 11h6" />
            </svg>
        </span>

        <span
            x-show="open"
            x-cloak
            class="text-2xl font-bold"
        >
            ✕
        </span>
    </button>

    <!-- نقل النافذة إلى body لمنع تعارضها مع تصميم الصفحة -->
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            dir="rtl"
            class="gym-chat-window"
            :class="{ 'fullscreen': fullscreen }"
            :style="{ '--gym-chat-vh': viewportHeight ? viewportHeight + 'px' : '100dvh', '--gym-chat-vtop': viewportTop + 'px' }"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="open = false"
        >
            <!-- جسم نافذة المحادثة -->
            <div
                class="gym-chat-panel bg-gray-900
                       border border-gray-700/80
                       rounded-3xl shadow-2xl
                       overflow-hidden flex flex-col
                       w-full h-full"
            >

                <!-- الهيدر -->
                <div
                    class="gym-chat-header bg-gradient-to-r
                           from-blue-900 via-indigo-900 to-gray-900
                           px-5 py-4 text-white
                           flex items-center justify-between
                           border-b border-gray-800
                           shrink-0"
                >
                    <!-- بيانات البوت -->
                    <div class="flex items-center gap-3 min-w-0">

                        <div
                            class="w-12 h-12 rounded-2xl
                                   bg-blue-600/30
                                   border border-blue-400/30
                                   flex items-center justify-center
                                   p-2 shrink-0"
                        >
                            <img
                                src="/images/bot.png"
                                alt="Bot"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline'"
                                class="w-full h-full object-contain"
                            >
                            <span style="display: none;" class="text-xl">🤖</span>
                        </div>

                        <div class="flex flex-col gap-1 min-w-0">

                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-bold text-base text-white">
                                    الكوتش الذكي
                                </h4>

                                <span
                                    class="text-[10px]
                                           bg-orange-500/20
                                           text-orange-400
                                           px-2 py-1 rounded-md
                                           border border-orange-500/30
                                           font-semibold"
                                >
                                    Gym Master
                                </span>
                            </div>

                            <span
                                class="text-xs text-emerald-400
                                       flex items-center gap-1"
                            >
                                <span class="text-emerald-400">●</span>
                                متصل وجاهز للمساعدة
                            </span>

                        </div>
                    </div>

                    <!-- أزرار التحكم -->
                    <div class="flex items-center gap-2 shrink-0">
                        <!-- محادثة جديدة: أيقونة SVG ثابتة وواضحة -->
                        <button
                            type="button"
                            @click="newChat()"
                            :disabled="loading"
                            title="محادثة جديدة"
                            aria-label="محادثة جديدة"
                            class="flex items-center justify-center shrink-0 rounded-xl border border-blue-400/40 text-blue-300 hover:text-white hover:bg-blue-600/20 disabled:opacity-40 transition-colors"
                            style="width: 42px; height: 42px; min-width: 42px; padding: 0;"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 width="21" height="21" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2.5"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 aria-hidden="true" style="display: block; flex-shrink: 0;">
                                <path d="M12 5v14" />
                                <path d="M5 12h14" />
                            </svg>
                        </button>

                        <!-- تكبير وتصغير -->
                        <button
                            type="button"
                            @click="fullscreen = !fullscreen; scrollToBottom()"
                            class="hidden sm:flex
                                   items-center justify-center
                                   w-10 h-10 rounded-xl
                                   text-gray-300 hover:text-white
                                   hover:bg-white/10
                                   transition-colors text-xl"
                            :title="fullscreen ? 'تصغير النافذة' : 'ملء الشاشة'"
                        >
                            <span x-show="!fullscreen">⛶</span>
                            <span x-show="fullscreen" x-cloak>❐</span>
                        </button>

                        <!-- إغلاق -->
                        <button
                            type="button"
                            @click="open = false"
                            class="flex items-center justify-center
                                   w-10 h-10 rounded-xl
                                   text-gray-300 hover:text-white
                                   hover:bg-red-500/20
                                   transition-colors
                                   text-xl font-bold"
                            title="إغلاق الشات"
                        >
                            ✕
                        </button>

                    </div>
                </div>

                <!-- منطقة الرسائل -->
                <div
                    x-ref="messagesContainer"
                    class="gym-chat-messages
                           flex-1 min-h-0
                           p-4 sm:p-6
                           overflow-y-auto
                           bg-gray-950/50"
                >
                    <div class="max-w-3xl mx-auto space-y-5">

                        <!-- رسالة الترحيب -->
                        <div class="gym-chat-welcome flex justify-start">
                            <div
                                class="bg-gray-800/90 text-gray-200
                                       p-4 rounded-2xl
                                       max-w-[95%] sm:max-w-[85%]
                                       border border-gray-700/80
                                       leading-relaxed shadow-sm"
                            >
                                <p class="font-bold text-blue-400 text-base mb-2">
                                    أهلاً بك يا {{ auth()->user()->name }}! 👋
                                </p>

                                <p class="text-gray-300 text-sm leading-7">
                                    أنا كوتشك الذكي في Gym Master.
                                    اسألني عن حساب السعرات والماكروز،
                                    تنظيم التغذية، جداول التمارين،
                                    أو أي نصيحة رياضية! 💪
                                </p>
                            </div>
                        </div>

                        <!-- اقتراحات سريعة: تظهر فقط في بداية المحادثة -->
                        <div x-show="messages.length === 0" class="gym-chat-suggestions flex flex-wrap gap-2 pt-2">
                            <template x-for="q in quickQuestions" :key="q">
                                <button type="button" class="gym-chat-quick"
                                        @click="askQuick(q)" :disabled="loading" x-text="q"></button>
                            </template>
                        </div>

                        <!-- رسائل المحادثة -->
                        <template x-for="(msg, index) in messages" :key="index">
                            <div class="flex w-full"
                                 :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'">
                                <div class="gym-chat-bubble min-w-0 p-4 rounded-2xl border shadow-md"
                                     :class="msg.sender === 'user'
                                        ? 'bg-blue-600 text-white border-blue-500 max-w-[90%] sm:max-w-[75%] rounded-tr-sm'
                                        : 'bg-gray-800 text-gray-200 border-gray-700 max-w-[97%] sm:max-w-[90%] rounded-tl-sm'">
                                    <div x-show="msg.sender === 'user'" class="gym-chat-text text-sm leading-7" x-text="msg.text"></div>
                                    <template x-if="msg.photoUrl"><img :src="msg.photoUrl" class="gym-chat-photo" alt="صورة الوجبة المرسلة"></template>
                                    <!-- Safe renderer only inserts markup it creates from HTML-escaped text -->
                                    <div x-show="msg.sender !== 'user' && !msg.calorie_result" class="gym-chat-rich" x-html="formatReply(msg.text)"></div>
                                    <div x-show="msg.sender !== 'user' && msg.calorie_result" class="gym-calorie-card" x-html="formatCalorieCard(msg.calorie_result)"></div>
                                    <template x-if="msg.mealDraft">
                                        <div class="gym-meal-review">
                                            <div x-show="!msg.review && !msg.mealSaved" class="gym-meal-review-actions">
                                                <button type="button" class="gym-meal-review-save" @click="openMealReview(msg)">راجع واحفظ الوجبة</button>
                                            </div>
                                            <form x-show="msg.review && !msg.mealSaved" @submit.prevent="saveMealDraft(msg)">
                                                <p class="gym-chat-photo-note">راجع التقدير أولًا. القيم لكل الكمية المكتوبة، وليست لكل 100 جم. تعديل الوزن يغيّر القيم نسبيًا، ويمكنك تعديلها يدويًا.</p>
                                                <fieldset :disabled="msg.mealSaving" style="min-width:0;border:0;padding:0;margin:0">
                                                    <div class="gym-meal-review-grid">
                                                        <label>اليوم بتوقيت القاهرة<input type="date" x-model="msg.mealDraft.date" min="2000-01-01" :max="msg.mealDraft.max_date" required aria-label="يوم تسجيل الوجبة"></label>
                                                        <label>نوع الوجبة<select x-model="msg.mealDraft.meal_type" required><option>إفطار</option><option>غداء</option><option>عشاء</option><option>سناك / وجبة خفيفة</option></select></label>
                                                    </div>
                                                    <template x-for="(item, itemIndex) in msg.mealDraft.items" :key="itemIndex">
                                                        <div class="gym-meal-review-item">
                                                            <div class="gym-meal-review-grid">
                                                                <label>اسم الصنف<input type="text" x-model="item.name" maxlength="120" required></label>
                                                                <label>الكمية بالجرام<input type="number" x-model.number="item.grams" @change="scaleMealItem(item)" inputmode="decimal" min="1" max="5000" step="0.01" required></label>
                                                            </div>
                                                            <div class="gym-meal-review-grid gym-meal-review-nutrients" style="margin-top:10px">
                                                                <label>السعرات (kcal)<input type="number" x-model.number="item.calories" inputmode="decimal" min="0" max="50000" step="0.01" required></label>
                                                                <label>البروتين (جم)<input type="number" x-model.number="item.protein" inputmode="decimal" min="0" max="5000" step="0.01" required></label>
                                                                <label>الكربوهيدرات (جم)<input type="number" x-model.number="item.carbs" inputmode="decimal" min="0" max="5000" step="0.01" required></label>
                                                                <label>الدهون (جم)<input type="number" x-model.number="item.fats" inputmode="decimal" min="0" max="5000" step="0.01" required></label>
                                                            </div>
                                                            <button type="button" class="gym-chat-action" style="margin-top:10px;min-height:40px" @click="msg.mealDraft.items.splice(itemIndex, 1)" :disabled="msg.mealDraft.items.length <= 1">حذف الصنف</button>
                                                        </div>
                                                    </template>
                                                    <p class="gym-meal-review-summary" x-text="mealTotals(msg.mealDraft.items)"></p>
                                                    <div class="gym-meal-review-actions">
                                                        <button type="submit" class="gym-meal-review-save" x-text="msg.mealSaving ? 'جاري الحفظ...' : 'تأكيد حفظ الوجبة'"></button>
                                                        <button type="button" class="gym-chat-action" @click="msg.review = false">رجوع بدون حفظ</button>
                                                    </div>
                                                </fieldset>
                                                <p x-show="msg.mealError" x-text="msg.mealError" class="gym-meal-review-error" role="alert"></p>
                                            </form>
                                            <div x-show="msg.mealSaved" role="status">
                                                <p>✓ تم حفظ الوجبة في سجل تغذيتك.</p>
                                                <div class="gym-meal-review-actions"><a :href="msg.mealUrl" class="gym-meal-review-save">عرض سجل اليوم</a></div>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="msg.sender !== 'user'" class="flex justify-end mt-3 pt-2 border-t border-gray-700/50">
                                        <button type="button" class="gym-chat-action" @click="copyReply(msg.text, index)"
                                                :aria-label="'نسخ الرد'" :title="'نسخ الرد'">
                                            <span x-text="copiedIndex === index ? '✓ تم النسخ' : '⧉ نسخ الرد'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- حالة انتظار رد البوت -->
                        <div x-show="loading" x-cloak class="flex justify-start">
                            <div
                                class="bg-gray-800 text-blue-400
                                       px-5 py-4 rounded-2xl
                                       border border-gray-700
                                       flex items-center gap-3"
                            >
                                <span class="animate-pulse text-sm">
                                    جاري تجهيز الإجابة...
                                </span>
                                <span class="animate-bounce">🤖</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- منطقة الكتابة والإرسال -->
                <div
                    class="gym-chat-composer bg-gray-900
                           border-t border-gray-800
                           p-3 sm:p-5 shrink-0"
                >
                    <div class="gym-chat-attachment" x-show="photoPreview" x-cloak>
                        <template x-if="photoPreview"><img :src="photoPreview" alt="معاينة صورة الوجبة"></template>
                        <span class="gym-chat-photo-note">صورة جاهزة للتحليل. اكتب الكمية وطريقة الطبخ إن أمكن.</span>
                        <button type="button" @click="clearPhoto()" :disabled="loading" class="gym-chat-action" aria-label="إزالة الصورة">✕</button>
                    </div>
                    <p x-show="photoError" x-text="photoError" role="alert" class="text-red-400 text-sm mb-2"></p>
                    <form
                        @submit.prevent="sendMessage()"
                        class="gym-chat-compose-form max-w-3xl mx-auto
                               flex items-center gap-3"
                    >
                        <input type="file" accept="image/jpeg,image/png,image/webp" x-ref="photoInput" @change="selectPhoto($event)" hidden>
                        <button type="button" class="gym-chat-upload" @click="$refs.photoInput.click()" :disabled="loading" aria-label="إرفاق صورة أكل" title="إرفاق صورة أكل">📷</button>
                        <!-- حقل كتابة الرسالة -->
                        <textarea
                            x-model="inputMessage"
                            x-ref="chatInput"
                            @input="resizeInput($el)"
                            @keydown="handleInputKeydown($event)"
                            :placeholder="photoFile ? 'الكمية وطريقة الطبخ (اختياري)...' : 'اسأل الكوتش أو ارفق صورة أكل...'"
                            maxlength="1000"
                            :disabled="loading"
                            rows="1"
                            dir="rtl"
                            class="gym-chat-input flex-1 min-w-0
                                   bg-gray-800 border border-gray-700
                                   text-white placeholder-gray-400
                                   rounded-2xl text-sm px-4 py-3.5
                                   focus:outline-none focus:border-blue-500
                                   focus:ring-1 focus:ring-blue-500
                                   transition-colors"
                            style="height: 52px; min-height: 52px; max-height: 150px;"
                        ></textarea>

                        <!-- زر الإرسال -->
                        <button
                            type="submit"
                            :disabled="loading || (!inputMessage.trim() && !photoFile)"
                            class="gym-chat-send bg-blue-600 hover:bg-blue-500
                                   disabled:opacity-40
                                   disabled:cursor-not-allowed
                                   text-white
                                   px-5 py-3.5
                                   rounded-2xl
                                   font-bold text-sm
                                   transition-all shadow-md
                                   shrink-0 flex items-center gap-2"
                        >
                            <span x-show="!loading">إرسال</span>
                            <span x-show="loading" x-cloak>انتظر...</span>
                            <span x-show="!loading">➤</span>
                        </button>
                    </form>

                    <p
                        class="text-center text-gray-500
                               text-[10px] sm:text-xs mt-3"
                    >
                        JPG / PNG / WebP حتى 5 ميجا. تُرسل الصورة إلى Gemini للتقدير؛ الوجبة لا تُحفظ في السجل إلا بعد مراجعتك وتأكيدك.
                    </p>
                </div>

            </div>
        </div>
    </template>
</div>

<script>
    function chatbot() {
        return {
            open: false,
            fullscreen: false,
            loading: false,
            inputMessage: '',
            viewportHeight: 0,
            viewportTop: 0,
            viewportHandler: null,
            photoFile: null,
            photoPreview: '',
            photoError: '',
            messages: [],
            copiedIndex: null,
            quickQuestions: ['وزني كام؟', 'احسب سعراتي', 'جدول تمرين للمبتدئين', 'أكلت كام سعرة النهارده؟'],

            init() {
                this.viewportHandler = () => {
                    const viewport = window.visualViewport;
                    this.viewportHeight = viewport?.height || window.innerHeight;
                    this.viewportTop = viewport?.offsetTop || 0;
                };
                this.viewportHandler();
                window.visualViewport?.addEventListener('resize', this.viewportHandler);
                window.visualViewport?.addEventListener('scroll', this.viewportHandler);
                window.addEventListener('resize', this.viewportHandler);
            },

            openMealReview(msg) {
                if (!msg.mealDraft || msg.mealSaved) return;
                msg.mealDraft.items.forEach(item => { item._grams = Number(item.grams); });
                msg.review = true;
                msg.mealError = '';
                this.scrollToBottom();
            },

            scaleMealItem(item) {
                const grams = Number(item.grams), previous = Number(item._grams);
                if (!Number.isFinite(grams) || grams < 1 || grams > 5000 || !previous) return;
                for (const key of ['calories', 'protein', 'carbs', 'fats']) {
                    item[key] = Math.round(Number(item[key]) * grams / previous * 100) / 100;
                }
                item._grams = grams;
            },

            mealTotals(items) {
                const sum = key => items.reduce((total, item) => total + (Number(item[key]) || 0), 0).toFixed(1);
                return `الإجمالي: ${sum('calories')} سعرة • بروتين ${sum('protein')} جم • كربوهيدرات ${sum('carbs')} جم • دهون ${sum('fats')} جم`;
            },

            async saveMealDraft(msg) {
                if (msg.mealSaving || msg.mealSaved || !msg.mealDraft) return;
                msg.mealSaving = true;
                msg.mealError = '';
                try {
                    const draft = msg.mealDraft;
                    const payload = {date: draft.date, meal_type: draft.meal_type, items: draft.items.map(item => ({
                        name: item.name, grams: item.grams, calories: item.calories, protein: item.protein, carbs: item.carbs, fats: item.fats,
                    }))};
                    const url = '{{ url("/chatbot/food-drafts") }}' + '/' + encodeURIComponent(draft.id) + '/save';
                    const response = await fetch(url, {method: 'POST', headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify(payload)});
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        msg.mealError = response.status === 422 ? (Object.values(data.errors || {}).flat()[0] || 'راجع بيانات الوجبة.')
                            : response.status === 410 ? 'انتهت صلاحية المراجعة. أرسل الصورة من جديد.'
                            : response.status === 409 ? 'سبق حفظ المراجعة بقيم أخرى. افتح سجل التغذية للتأكد.'
                            : [401, 419].includes(response.status) ? 'حدّث الصفحة وسجّل الدخول ثم حاول مرة أخرى.'
                            : response.status === 429 ? 'انتظر دقيقة قبل إعادة المحاولة.'
                            : 'تعذر الحفظ. حاول مرة أخرى؛ إعادة المحاولة لن تكرر الوجبة.';
                        return;
                    }
                    if (data.saved === true) {
                        msg.mealSaved = true;
                        msg.mealUrl = data.url;
                        msg.review = false;
                    } else {
                        msg.mealError = 'تعذر تأكيد الحفظ. راجع سجل التغذية أو حاول مرة أخرى.';
                    }
                } catch {
                    msg.mealError = 'الاتصال انقطع. اضغط تأكيد الحفظ مرة أخرى؛ الوجبة لن تتكرر.';
                } finally {
                    msg.mealSaving = false;
                    this.scrollToBottom();
                }
            },

            selectPhoto(event) {
                if (this.loading || this.messages.some(msg => msg.mealSaving)) return;
                const file = event.target.files?.[0];
                this.clearPhoto();
                if (!file) return;
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
                    this.photoError = 'اختار صورة JPG أو PNG أو WebP بحجم لا يتجاوز 5 ميجا.';
                    event.target.value = '';
                    return;
                }
                this.photoFile = file;
                this.photoPreview = URL.createObjectURL(file);
            },

            clearPhoto() {
                if (this.photoPreview) URL.revokeObjectURL(this.photoPreview);
                this.photoFile = null;
                this.photoPreview = '';
                this.photoError = '';
                if (this.$refs.photoInput) this.$refs.photoInput.value = '';
            },

            destroy() {
                window.visualViewport?.removeEventListener('resize', this.viewportHandler);
                window.visualViewport?.removeEventListener('scroll', this.viewportHandler);
                window.removeEventListener('resize', this.viewportHandler);
                this.clearPhoto();
                this.messages.forEach(m => { if (m.photoUrl) URL.revokeObjectURL(m.photoUrl); });
            },

            newChat() {
                if (this.loading || this.messages.some(msg => msg.mealSaving)) return;
                if (this.messages.length && !window.confirm('تبدأ محادثة جديدة؟ سيتم مسح الرسائل الحالية من هذه النافذة.')) return;
                this.messages.forEach(m => { if (m.photoUrl) URL.revokeObjectURL(m.photoUrl); });
                this.messages = [];
                this.clearPhoto();
                this.inputMessage = '';
                this.copiedIndex = null;
                this.$nextTick(() => { this.resizeInput(this.$refs.chatInput); this.$refs.chatInput?.focus(); });
            },

            askQuick(question) {
                if (this.loading) return;
                this.inputMessage = question;
                this.clearPhoto();
                this.sendMessage();
            },

            async copyReply(value, index) {
                const text = String(value ?? '');
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(text);
                    } else {
                        const temp = document.createElement('textarea');
                        temp.value = text;
                        temp.style.position = 'fixed';
                        temp.style.opacity = '0';
                        document.body.appendChild(temp);
                        temp.select();
                        const ok = document.execCommand('copy');
                        temp.remove();
                        if (!ok) throw new Error('Copy not available');
                    }
                    this.copiedIndex = index;
                } catch (e) {
                    console.warn('Copy failed:', e);
                    alert('تعذر النسخ تلقائيًا، يمكنك تحديد النص ونسخه يدويًا.');
                }
            },

            escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            },

            formatInline(value) {
                // Input is HTML-escaped first; replace only small Markdown syntax.
                let safe = this.escapeHtml(value);
                safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                safe = safe.replace(/`([^`]+)`/g, '<code>$1</code>');
                return safe;
            },

            formatReply(value) {
                // Avoid passing raw Gemini HTML into x-html. The renderer escapes everything first.
                const lines = String(value ?? '').replace(/\r\n?/g, '\n').split('\n');
                let out = '', list = null;
                const closeList = () => { if (list) { out += '</' + list + '>'; list = null; } };
                for (const rawLine of lines) {
                    const line = rawLine.trim();
                    if (!line) { closeList(); continue; }
                    const header = line.match(/^(#{1,3})\s+(.+)$/);
                    const numbered = line.match(/^\d+[.)]\s+(.+)$/);
                    const bulleted = line.match(/^[-*•]\s+(.+)$/);
                    if (header) {
                        closeList(); out += '<' + (header[1].length <= 2 ? 'h3' : 'h4') + '>' + this.formatInline(header[2]) + '</' + (header[1].length <= 2 ? 'h3' : 'h4') + '>';
                    } else if (numbered || bulleted) {
                        const type = numbered ? 'ol' : 'ul';
                        if (list !== type) { closeList(); out += '<' + type + '>'; list = type; }
                        out += '<li>' + this.formatInline((numbered || bulleted)[1]) + '</li>';
                    } else if (/^[-_]{3,}$/.test(line)) {
                        closeList(); out += '<hr>';
                    } else {
                        closeList(); out += '<p>' + this.formatInline(line) + '</p>';
                    }
                }
                closeList();
                return out;
            },

            formatCalorieCard(data) {
                if (!data || typeof data !== 'object') return '';
                const n = (key) => Number.isFinite(Number(data[key])) ? Math.max(0, Math.round(Number(data[key]))) : 0;
                const fmt = (value) => value.toLocaleString('en-US');
                const goal = ({cut: 'تنشيف', bulk: 'زيادة وزن', maintain: 'ثبات'})[data.goal] || 'هدف السعرات';
                const safeGoal = this.escapeHtml(goal);
                const total = n('protein') * 4 + n('carbs') * 4 + n('fat') * 9;
                const macro = (label, grams, kcal, color) => {
                    const percent = total ? Math.min(100, Math.max(0, kcal / total * 100)) : 0;
                    return `<div class="gym-calorie-row"><span>${label}</span><strong>${fmt(grams)} جم</strong></div><div class="gym-calorie-track"><div class="gym-calorie-fill" style="width:${percent.toFixed(1)}%;background:${color}"></div></div>`;
                };
                return `<div class="flex items-center justify-between gap-2"><strong>🧮 حاسبة السعرات والماكروز</strong><span class="text-green-300 text-xs">✓ تم الحساب</span></div>
                    <div class="gym-calorie-hero"><div class="gym-calorie-muted">سعراتك اليومية • ${safeGoal}</div><strong>${fmt(n('calories'))}</strong><div class="gym-calorie-muted">سعرة / يوم</div></div>
                    <div class="gym-calorie-grid"><div class="gym-calorie-tile"><div class="gym-calorie-muted">🔥 الحرق الأساسي BMR</div><strong>${fmt(n('bmr'))}</strong><div class="gym-calorie-muted">سعرة / يوم</div></div><div class="gym-calorie-tile"><div class="gym-calorie-muted">⚡ الثبات TDEE</div><strong>${fmt(n('tdee'))}</strong><div class="gym-calorie-muted">سعرة / يوم</div></div></div>
                    <div class="gym-calorie-macros"><strong>توزيع الماكروز اليومي</strong>${macro('البروتين', n('protein'), n('protein') * 4, '#60a5fa')}${macro('الكارب', n('carbs'), n('carbs') * 4, '#4ade80')}${macro('الدهون', n('fat'), n('fat') * 9, '#fbbf24')}</div>
                    <p class="gym-calorie-muted mt-3">دي تقديرات مبدئية وليست وصفة طبية. راقب التغير الفعلي وعدّل مع مختص عند الحاجة.</p>`;
            },

            handleInputKeydown(event) {
                if (event.key !== 'Enter' || event.isComposing || event.keyCode === 229) return;

                // Shift + Enter يضيف سطرًا جديدًا بشكل طبيعي.
                if (event.shiftKey) return;

                // Enter وحده يمنع السطر الجديد ويرسل الرسالة.
                // يتم تجاهل اختصارات Ctrl/Alt/Meta.
                if (event.ctrlKey || event.altKey || event.metaKey) return;

                event.preventDefault();
                if (!this.loading && (this.inputMessage.trim() || this.photoFile)) {
                    this.sendMessage();
                }
            },

            async sendMessage() {
                if (this.loading || (!this.inputMessage.trim() && !this.photoFile)) {
                    return;
                }

                const userText = this.inputMessage.trim();
                const photo = this.photoFile;
                const messagePhoto = photo ? URL.createObjectURL(photo) : null;
                this.clearPhoto();

                // تجهيز سجل المحادثة قبل إضافة الرسالة الجديدة
                const historyPayload = this.messages.filter(m => !m.photoUrl && !m.photoRelated).slice(-12).map(m => ({
                    sender: m.sender,
                    text: String(m.text).slice(0, 5000)
                }));

                // إضافة رسالة المستخدم
                this.messages.push({
                    sender: 'user',
                    text: userText || 'قدّر سعرات وماكروز الصورة',
                    photoUrl: messagePhoto
                });

                this.inputMessage = '';
                this.$nextTick(() => this.resizeInput(this.$refs.chatInput));
                this.loading = true;

                this.scrollToBottom();

                try {
                    const body = new FormData();
                    if (userText) body.append('message', userText);
                    if (photo) body.append('photo', photo);
                    if (!photo) historyPayload.forEach((m, i) => {
                        body.append(`history[${i}][sender]`, m.sender);
                        body.append(`history[${i}][text]`, m.text);
                    });
                    const response = await fetch('{{ route("chatbot.send") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body
                    });

                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        const validation = Object.values(errorData.errors || {}).flat()[0];
                        this.messages.push({sender: 'bot', photoRelated: !!photo, text: response.status === 422
                            ? (validation || 'راجع نوع الصورة وحجمها والرسالة.')
                            : response.status === 413 ? 'الصورة أكبر من حد الرفع على السيرفر. جرّب صورة أصغر.'
                            : response.status === 429 ? 'طلبات كثيرة. انتظر دقيقة وحاول مرة أخرى.'
                            : [401, 419].includes(response.status) ? 'انتهت الجلسة. حدّث الصفحة وسجّل الدخول مرة أخرى.'
                            : 'تعذر إكمال الطلب. جرّب مرة أخرى.'});
                        return;
                    }

                    const data = await response.json();

                    // إضافة رد البوت
                    this.messages.push({
                        sender: 'bot',
                        photoRelated: !!photo,
                        mealDraft: data.meal_draft || null,
                        review: false, mealSaving: false, mealSaved: false, mealError: '', mealUrl: '',
                        text: typeof data.reply === 'string'
                            ? data.reply
                            : 'لم أتمكن من تجهيز الرد. حاول مرة أخرى.',
                        calorie_result: data.calorie_result && typeof data.calorie_result === 'object'
                            ? data.calorie_result
                            : null
                    });

                } catch (error) {
                    console.error('Chatbot Error:', error);

                    this.messages.push({
                        sender: 'bot',
                        text: 'حدث خطأ في الاتصال، حاول مرة أخرى.'
                    });

                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                }
            },

            resizeInput(el) {
                if (!el) return;
                el.style.height = '52px';
                el.style.height = Math.max(52, Math.min(el.scrollHeight, 150)) + 'px';
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const el = this.$refs.messagesContainer;

                    if (el) {
                        el.scrollTop = el.scrollHeight;
                    }
                });
            }
        };
    }
</script>
