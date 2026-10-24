
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
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        transform: none !important;
        width: 100vw !important;
        height: 100dvh !important;
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

    @media (max-width: 640px) {
        .gym-chat-window {
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            transform: none !important;
            width: 100vw !important;
            height: 100dvh !important;
        }

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
        class="bg-blue-600 hover:bg-blue-500
               text-white shadow-2xl
               flex items-center justify-center
               transition-all hover:scale-110
               active:scale-95
               border-2 border-blue-400/40
               cursor-pointer overflow-hidden"
        style="
            width: 58px !important;
            height: 58px !important;
            min-width: 58px !important;
            min-height: 58px !important;
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
                    class="bg-gradient-to-r
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
                        <div class="flex justify-start">
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
                        <div x-show="messages.length === 0" class="flex flex-wrap gap-2 pt-2">
                            <template x-for="q in quickQuestions" :key="q">
                                <button type="button" class="gym-chat-quick"
                                        @click="askQuick(q)" :disabled="loading" x-text="q"></button>
                            </template>
                        </div>

                        <!-- رسائل المحادثة -->
                        <template x-for="(msg, index) in messages" :key="index">
                            <div class="flex w-full"
                                 :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'">
                                <div class="min-w-0 p-4 rounded-2xl border shadow-md"
                                     :class="msg.sender === 'user'
                                        ? 'bg-blue-600 text-white border-blue-500 max-w-[90%] sm:max-w-[75%] rounded-tr-sm'
                                        : 'bg-gray-800 text-gray-200 border-gray-700 max-w-[97%] sm:max-w-[90%] rounded-tl-sm'">
                                    <div x-show="msg.sender === 'user'" class="gym-chat-text text-sm leading-7" x-text="msg.text"></div>
                                    <!-- Safe renderer only inserts markup it creates from HTML-escaped text -->
                                    <div x-show="msg.sender !== 'user' && !msg.calorie_result" class="gym-chat-rich" x-html="formatReply(msg.text)"></div>
                                    <div x-show="msg.sender !== 'user' && msg.calorie_result" class="gym-calorie-card" x-html="formatCalorieCard(msg.calorie_result)"></div>
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
                    class="bg-gray-900
                           border-t border-gray-800
                           p-3 sm:p-5 shrink-0"
                >
                    <form
                        @submit.prevent="sendMessage()"
                        class="max-w-3xl mx-auto
                               flex items-center gap-3"
                    >
                        <!-- حقل كتابة الرسالة -->
                        <textarea
                            x-model="inputMessage"
                            x-ref="chatInput"
                            @input="resizeInput($el)"
                            @keydown="handleInputKeydown($event)"
                            placeholder="اسأل الكوتش الذكي..."
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
                            :disabled="loading || !inputMessage.trim()"
                            class="bg-blue-600 hover:bg-blue-500
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
                        اضغط Enter للإرسال و Shift + Enter لسطر جديد
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
            messages: [],
            copiedIndex: null,
            quickQuestions: ['وزني كام؟', 'احسب سعراتي', 'جدول تمرين للمبتدئين', 'أكلت كام سعرة النهارده؟'],

            newChat() {
                if (this.loading) return;
                if (this.messages.length && !window.confirm('تبدأ محادثة جديدة؟ سيتم مسح الرسائل الحالية من هذه النافذة.')) return;
                this.messages = [];
                this.inputMessage = '';
                this.copiedIndex = null;
                this.$nextTick(() => { this.resizeInput(this.$refs.chatInput); this.$refs.chatInput?.focus(); });
            },

            askQuick(question) {
                if (this.loading) return;
                this.inputMessage = question;
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
                if (!this.loading && this.inputMessage.trim()) {
                    this.sendMessage();
                }
            },

            async sendMessage() {
                if (this.loading || !this.inputMessage.trim()) {
                    return;
                }

                const userText = this.inputMessage.trim();

                // تجهيز سجل المحادثة قبل إضافة الرسالة الجديدة
                const historyPayload = this.messages.map(m => ({
                    sender: m.sender,
                    text: m.text
                }));

                // إضافة رسالة المستخدم
                this.messages.push({
                    sender: 'user',
                    text: userText
                });

                this.inputMessage = '';
                this.$nextTick(() => this.resizeInput(this.$refs.chatInput));
                this.loading = true;

                this.scrollToBottom();

                try {
                    const response = await fetch('{{ route("chatbot.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            message: userText,
                            history: historyPayload
                        })
                    });

                    if (!response.ok) {
                        throw new Error('HTTP Error: ' + response.status);
                    }

                    const data = await response.json();

                    // إضافة رد البوت
                    this.messages.push({
                        sender: 'bot',
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
