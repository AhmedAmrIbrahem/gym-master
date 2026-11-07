<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
                <span>📊</span> الشاشة الرئيسية والأنشطة
            </h2>
            <a href="#today-workout-selector" data-open-workout-selector class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2">
                <span>➕</span> تسجيل تمرين اليوم / السجل
            </a>
        </div>
    </x-slot>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const startLinks = document.querySelectorAll('[data-open-workout-selector]');
            const workoutCard = document.getElementById('today-workout-selector');
            if (!startLinks.length || !workoutCard) return;

            startLinks.forEach(startLink => startLink.addEventListener('click', (event) => {
                event.preventDefault();
                const dayPicker = workoutCard.querySelector('[data-gym-day-toggle]');
                if (dayPicker) {
                    dayPicker.click();
                    return;
                }
                workoutCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }));
        });
    </script>

    <!-- تضمين مكتبة Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="space-y-6">

        <!-- 1. كروت الملخص السريع (KPI Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- كارت تمرين اليوم: اختيار اليوم قبل الذهاب إلى شاشة التسجيل -->
            <x-workout.today-workout-card :active-program="$activeProgram" :today-workout="$todayWorkout" />

            <!-- كارت سعرات اليوم -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700/80 shadow-xl flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400">🔥 سعرات اليوم</p>
                        <h4 class="text-2xl font-black text-amber-400 mt-1">
                            {{ number_format($todayCalories) }} <span class="text-xs text-gray-400 font-normal">كالوري</span>
                        </h4>
                    </div>
                    <a href="{{ route('nutrition.index') }}" class="w-10 h-10 bg-amber-600/20 border border-amber-500/40 rounded-xl flex items-center justify-center text-xl shrink-0">
                        🥗
                    </a>
                </div>

                <div class="pt-2 border-t border-gray-700/60 flex items-center justify-between">
                    <span class="text-[11px] text-emerald-400 font-bold">🥩 {{ number_format($todayProtein, 1) }}ج بروتين</span>
                    <a href="{{ route('nutrition.index') }}" class="text-[11px] text-gray-400 hover:text-white font-bold transition">التفاصيل ←</a>
                </div>
            </div>

            <!-- كارت الوزن الحالي -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700/80 shadow-xl flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400">⚖️ أحدث وزن</p>
                        <h4 class="text-2xl font-black text-emerald-400 mt-1">
                            {{ $latestMeasurement->weight ?? '--' }} <span class="text-xs text-gray-400 font-normal">كجم</span>
                        </h4>
                    </div>
                    <a href="{{ route('measurements.index') }}" class="w-10 h-10 bg-emerald-600/20 border border-emerald-500/40 rounded-xl flex items-center justify-center text-xl shrink-0">
                        📏
                    </a>
                </div>

                <div class="pt-2 border-t border-gray-700/60 flex items-center justify-between">
                    <span class="text-[10px] text-gray-400">
                        {{ $latestMeasurement ? Carbon\Carbon::parse($latestMeasurement->date)->format('Y-m-d') : 'لا يوجد قياس' }}
                    </span>
                    <a href="{{ route('measurements.index') }}" class="text-[11px] text-emerald-400 hover:underline font-bold">+ إضافة وزن</a>
                </div>
            </div>

            <!-- كارت الكرياتين السريع -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700/80 shadow-xl flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400">💊 جرعة الكرياتين</p>
                        <h4 class="text-xs font-bold mt-1 {{ $creatineTakenToday ? 'text-emerald-400' : 'text-amber-400' }}">{{ $creatineTakenToday ? 'تم تناول 5 جرام ✓' : 'لم تُسجل بعد' }}</h4>
                    </div>
                    <div class="w-10 h-10 bg-purple-600/20 border border-purple-500/40 rounded-xl flex items-center justify-center text-xl shrink-0">
                        ⚡
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-700/60">
                    <form method="POST" action="{{ route('dashboard.creatine.toggle') }}">
                        @csrf
                        <button type="submit" class="w-full text-[11px] font-bold py-1.5 rounded-lg transition shadow-md {{ $creatineTakenToday ? 'bg-gray-700 text-gray-200 hover:bg-gray-600' : 'bg-purple-600 text-white hover:bg-purple-500' }}">
                            {{ $creatineTakenToday ? 'إلغاء التحديد' : '✓ أخذت الكرياتين اليوم' }}
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Dashboard layout: 4 KPIs above, then 3 / 5 / 4, 5 / 3 / 4, 6 / 6. --}}
        <style>
            .gym-home { --gh-card:#1c293c; --gh-soft:#26364d; --gh-border:#314158; --gh-text:#f1f5f9; --gh-muted:#96a9c2; --gh-track:#3b4b60; }
            html[data-theme="light"] .gym-home { --gh-card:#fff; --gh-soft:#f1f5fa; --gh-border:#dce5f1; --gh-text:#172b49; --gh-muted:#687b96; --gh-track:#e4eaf3; }
            .gym-home .gh-card {position:relative;background:var(--gh-card);border:1px solid var(--gh-border);color:var(--gh-text);box-shadow:0 3px 12px rgba(3,10,22,.10);transition:border-color .22s ease,transform .22s ease,box-shadow .22s ease;}
            .gym-home .gh-card:hover {border-color:rgba(82,139,225,.55);box-shadow:0 12px 30px rgba(2,10,24,.19);transform:translateY(-2px)}
            .gym-home .gh-card:focus-within {border-color:#3b82f6}
            .gym-home .gh-kpi {min-height:158px;overflow:visible}
            .gym-home .gh-kpi::before {content:"";position:absolute;inset-inline-start:18px;inset-inline-end:18px;top:0;height:2px;border-radius:999px;background:linear-gradient(90deg,transparent,rgba(96,165,250,.62),transparent);opacity:.7}
            .gym-home .gh-kpi h4 {line-height:1.35}
            .gym-home .gh-kpi a,.gym-home .gh-kpi button {transition:background-color .2s ease,transform .2s ease}
            .gym-home .gh-kpi a:hover,.gym-home .gh-kpi button:hover {transform:translateY(-1px)}
            .gym-home .gh-card h3 {letter-spacing:-.015em;line-height:1.5}
            .gym-home .gh-card canvas {max-width:100%}
            .gym-home .gh-card .gh-soft {border:1px solid rgba(148,163,184,.045)}
            .gym-home .gh-card .gh-chart-empty {border:1px dashed var(--gh-border);background:transparent}
            html[data-theme="light"] .gym-home .gh-card {box-shadow:0 3px 12px rgba(25,58,98,.055)}
            html[data-theme="light"] .gym-home .gh-card:hover {box-shadow:0 12px 28px rgba(25,58,98,.105)}
            html[data-theme="light"] .gym-home .gh-kpi .text-white {color:var(--gh-text)!important}
            @media(prefers-reduced-motion:reduce){.gym-home .gh-card,.gym-home .gh-kpi a,.gym-home .gh-kpi button{transition:none!important}.gym-home .gh-card:hover,.gym-home .gh-kpi a:hover,.gym-home .gh-kpi button:hover{transform:none}}
            .gym-home .gh-muted{color:var(--gh-muted)} .gym-home .gh-soft{background:var(--gh-soft)}
            .gym-home .gh-rule{border-color:var(--gh-border)} .gym-home .gym-dash-ring-track{stroke:var(--gh-track)}
            .gym-home .gym-dash-time{color:var(--gh-text)}
            .gym-home [data-timer-dash-live][hidden],.gym-home [data-timer-dash-idle][hidden],.gym-home [data-timer-dash-toggle][hidden]{display:none!important}
            .gym-home .gym-dash-ring-fill{transition:stroke-dashoffset .25s linear,stroke .2s ease}
            .gym-home [data-timer-dashboard][data-status="paused"] .gym-dash-indicator{color:#f59e0b;background:rgba(245,158,11,.12)}
            .gym-home [data-timer-dashboard][data-status="finished"] .gym-dash-indicator{color:#10b981;background:rgba(16,185,129,.12)}
            .gym-home .gh-chart-empty{background:var(--gh-soft);color:var(--gh-muted)}
            .gym-home .gh-progress{background:var(--gh-track)}
            .gym-home .gh-workout-card { background:linear-gradient(135deg,#20344e 0%,var(--gh-card) 82%); }
            html[data-theme="light"] .gym-home .gh-workout-card { background:linear-gradient(135deg,#edf5ff 0%,#fff 80%); }
            .gym-home .gh-water-track { background:var(--gh-track); }
            @media(prefers-reduced-motion:reduce){.gym-home .gym-dash-ring-fill{transition:none}}

            .gym-home .gh-section-card{min-height:100%}
            .gym-home .gh-panel-title{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem}
            .gym-home .gh-panel-note{margin-top:.2rem;font-size:.72rem;line-height:1.5;color:var(--gh-muted)}
            .gym-home .gh-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .7rem;border-radius:999px;border:1px solid rgba(59,130,246,.18);background:rgba(59,130,246,.08);font-size:.68rem;font-weight:800;color:#93c5fd}
            .gym-home .gh-action{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;min-height:2.7rem;border-radius:1rem;padding:.7rem 1rem;font-size:.75rem;font-weight:800;transition:background-color .2s ease,transform .2s ease,border-color .2s ease,color .2s ease}
            .gym-home .gh-action:hover{transform:translateY(-1px)}
            .gym-home .gh-soft-card{border:1px solid rgba(148,163,184,.08);background:linear-gradient(180deg, rgba(255,255,255,.025), rgba(255,255,255,0)),var(--gh-soft)}
            .gym-home .gh-popover details>summary{list-style:none}
            .gym-home .gh-popover details>summary::-webkit-details-marker{display:none}
            .gym-home .gh-popover details[open]>.gh-popover-panel{opacity:1;visibility:visible;transform:translateY(0) scale(1)}
            .gym-home .gh-popover-panel{position:absolute;inset-inline:0;bottom:calc(100% + .75rem);z-index:30;opacity:0;visibility:hidden;transform:translateY(.35rem) scale(.98);transform-origin:bottom center;transition:opacity .18s ease,transform .18s ease,visibility .18s ease;padding:1rem;border-radius:1rem;border:1px solid var(--gh-border);background:rgba(20,30,45,.97);box-shadow:0 18px 40px rgba(2,10,24,.28);backdrop-filter:blur(10px)}
            html[data-theme="light"] .gym-home .gh-popover-panel{background:rgba(255,255,255,.98);box-shadow:0 16px 32px rgba(25,58,98,.14)}
            .gym-home .gh-popover-panel::after{content:"";position:absolute;right:1.25rem;top:100%;border-width:8px;border-style:solid;border-color:rgba(20,30,45,.97) transparent transparent transparent}
            html[data-theme="light"] .gym-home .gh-popover-panel::after{border-top-color:rgba(255,255,255,.98)}
            .gym-home .gh-hero-banner{border:1px solid rgba(59,130,246,.12);background:linear-gradient(135deg, rgba(59,130,246,.16), rgba(59,130,246,0) 70%);}
            .gym-home .gh-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}
            .gym-home .gh-mini-tile{padding:.85rem;border-radius:1rem;border:1px solid rgba(148,163,184,.08);background:rgba(255,255,255,.025)}
            .gym-home .gh-mini-label{display:block;font-size:.68rem;color:var(--gh-muted)}
            .gym-home .gh-mini-value{display:block;margin-top:.35rem;font-size:.95rem;font-weight:800}

        </style>
        <div class="gym-home space-y-4" dir="rtl">
            {{-- Main row: stopwatch, calorie trend, quick workout. --}}
            <div class="grid grid-cols-1 items-stretch gap-4 lg:grid-cols-12">
                <section data-timer-dashboard data-status="idle" aria-label="الساعة والمؤقت" class="gh-card gh-section-card flex min-w-0 flex-col rounded-2xl p-5 lg:col-span-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0"><h3 class="text-sm font-extrabold">الساعة والمؤقت</h3><p data-timer-dash-status class="gh-muted mt-1 text-[11px]">جاهز للاستخدام</p></div>
                        <span class="gym-dash-indicator flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600/10 text-blue-500">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="13" r="8"/><path d="M12 8v5l3 2M9 2h6"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex flex-1 flex-col items-center justify-center gap-3">
                        <div data-timer-dash-live hidden class="relative h-36 w-36 shrink-0" role="timer" aria-label="الوقت الحالي">
                            <svg class="h-full w-full" viewBox="0 0 120 120" aria-hidden="true">
                                <circle class="gym-dash-ring-track" cx="60" cy="60" r="52" fill="none" stroke-width="7"/>
                                <circle data-timer-dash-ring class="gym-dash-ring-fill" cx="60" cy="60" r="52" fill="none" stroke="#3b82f6" stroke-width="7" stroke-linecap="round" stroke-dasharray="326.726" stroke-dashoffset="326.726" transform="rotate(-90 60 60)"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 px-3 text-center">
                                <span data-timer-dash-time dir="ltr" class="gym-dash-time max-w-full font-mono text-[25px] font-extrabold tabular-nums tracking-tight">00:00</span>
                                <span data-timer-dash-caption class="gh-muted text-[10px]">الوقت المتبقي</span>
                            </div>
                        </div>
                        <div data-timer-dash-idle class="gh-soft flex h-36 w-36 flex-col items-center justify-center rounded-full text-center">
                            <svg class="text-blue-500" width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="13" r="8"/><path d="M12 8v5l3 2M9 2h6"/></svg>
                            <span class="gh-muted mt-2 text-[11px]">ابدأ التوقيت</span>
                        </div>
                        <div class="flex w-full gap-2">
                            <button data-timer-dash-toggle type="button" hidden class="min-h-10 min-w-0 flex-1 rounded-xl bg-blue-600 px-2 py-2 text-[11px] font-bold text-white transition hover:bg-blue-500" aria-label="إيقاف مؤقت أو متابعة"><span data-timer-dash-toggle-text>إيقاف مؤقت</span></button>
                            <button type="button" data-timer-open class="min-h-10 min-w-0 flex-1 rounded-xl border border-blue-500/30 bg-blue-500/10 px-2 py-2 text-[11px] font-bold text-blue-500 transition hover:bg-blue-500/20"><span data-timer-dash-open-text>فتح الساعة</span></button>
                        </div>
                    </div>
                </section>

                <section class="gh-card flex min-w-0 flex-col rounded-2xl p-5 lg:col-span-5">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div><h3 class="text-sm font-extrabold">السعرات خلال الأسبوع</h3><p class="gh-muted mt-1 text-[11px]">آخر 7 أيام</p></div>
                        <a href="{{ route('nutrition.index') }}" class="text-[11px] font-bold text-blue-500 hover:underline">سجل التغذية ←</a>
                    </div>
                    @if($weeklyCaloriesLogged)
                        <div class="mt-4 h-48 min-w-0 flex-1"><canvas id="nutritionChart" aria-label="سعرات آخر سبعة أيام" role="img"></canvas></div>
                    @else
                        <div class="gh-chart-empty mt-4 flex min-h-44 flex-1 flex-col items-center justify-center rounded-xl px-4 py-5 text-center">
                            <p class="text-xs font-bold">مفيش وجبات مسجلة خلال الأسبوع</p>
                            <a href="{{ route('nutrition.index') }}" class="mt-3 text-[11px] font-bold text-blue-500 hover:underline">سجّل أول وجبة ←</a>
                        </div>
                    @endif
                </section>
                {{-- Refined workout CTA card. --}}
                <section class="gh-card gh-workout-card gh-section-card flex min-w-0 flex-col rounded-2xl p-5 lg:col-span-4" aria-label="بدء التمرين">
                    <div class="gh-panel-title">
                        <div class="min-w-0">
                            <span class="gh-chip">{{ $todayWorkout ? '✓ الجلسة مسجلة' : '⚡ خطوتك التالية' }}</span>
                            <h3 class="mt-3 text-xl font-extrabold leading-snug">{{ $todayWorkout ? 'تمرين اليوم تم بنجاح' : 'جاهز تبدأ تمرينك؟' }}</h3>
                            <p class="gh-panel-note mt-2">{{ $activeProgram ? 'النظام الحالي: '.$activeProgram->name.' — اختَر يومك وابدأ التتبع مباشرة.' : 'فعّل برنامجك التدريبي أولًا علشان تظهر لك أيام التمرين وتبدأ التسجيل.' }}</p>
                        </div>
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10 text-blue-400" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7v10m12-10v10M3 9v6m18-6v6M6 12h12"/></svg>
                        </span>
                    </div>

                    <div class="gh-hero-banner mt-5 rounded-2xl p-4">
                        <div class="gh-mini-grid">
                            <div class="gh-mini-tile">
                                <span class="gh-mini-label">الحالة</span>
                                <span class="gh-mini-value {{ $todayWorkout ? 'text-emerald-400' : 'text-amber-400' }}">{{ $todayWorkout ? 'منجز اليوم' : 'بانتظار البدء' }}</span>
                            </div>
                            <div class="gh-mini-tile">
                                <span class="gh-mini-label">النظام</span>
                                <span class="gh-mini-value truncate">{{ $activeProgram->name ?? 'غير محدد' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                        @if($activeProgram && $activeProgram->days->isNotEmpty())
                            <a href="#today-workout-selector" data-open-workout-selector class="gh-action w-full bg-blue-600 text-white hover:bg-blue-500 sm:w-auto">{{ $todayWorkout ? 'عرض أيام التمرين' : 'ابدأ تمرين اليوم' }} <span aria-hidden="true">←</span></a>
                        @else
                            <a href="{{ route('programs.index') }}" class="gh-action w-full bg-blue-600 text-white hover:bg-blue-500 sm:w-auto">{{ $activeProgram ? 'أضف أيام التمرين' : 'إعداد نظام التمارين' }} <span aria-hidden="true">←</span></a>
                        @endif
                        <a href="{{ route('history.index') }}" class="gh-action w-full border border-blue-500/25 bg-blue-500/10 text-blue-300 hover:bg-blue-500/15 sm:w-auto">سجل التمارين</a>
                    </div>
                </section>
            </div>

            {{-- Middle row: actual targets, macro overview and weekly activity. --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
                <section class="gh-card min-w-0 rounded-2xl p-4 xl:col-span-5 xl:p-5">
                    <div class="flex items-start justify-between gap-2">
                        <div><h3 class="text-sm font-extrabold">تقدم أهدافك الغذائية</h3><p class="gh-muted mt-1 text-[11px]">مقارنة بما سجلته اليوم</p></div>
                        <a href="{{ route('profile.edit') }}" class="text-[11px] font-bold text-blue-500 hover:underline">تعديل الأهداف ←</a>
                    </div>
                    <div class="mt-5 space-y-4">
                        @forelse($nutritionGoals as $goal)
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-2 text-[11px]"><span class="font-bold">{{ $goal['label'] }}</span><span class="gh-muted tabular-nums">{{ number_format($goal['actual'], $goal['precision']) }} / {{ number_format($goal['target'], $goal['precision']) }} {{ $goal['unit'] }}</span></div>
                                <div class="gh-progress h-2 overflow-hidden rounded-full" role="progressbar" aria-label="{{ $goal['label'] }}" aria-valuenow="{{ $goal['percentage'] }}" aria-valuemin="0" aria-valuemax="100"><div class="h-full rounded-full {{ $goal['color'] }}" style="width:{{ $goal['percentage'] }}%"></div></div>
                            </div>
                        @empty
                            <div class="gh-chart-empty rounded-xl px-4 py-4 text-center"><p class="text-xs font-bold">أهدافك الغذائية لسه مش محددة</p><a href="{{ route('profile.edit') }}" class="mt-2 inline-block text-[11px] font-bold text-blue-500 hover:underline">تحديد الأهداف ←</a></div>
                        @endforelse
                    </div>
                </section>
                <section class="gh-card min-w-0 rounded-2xl p-4 md:col-span-1 xl:col-span-3 xl:p-5">
                    <div><h3 class="text-sm font-extrabold">ملخص التغذية</h3><p class="gh-muted mt-1 text-[11px]">إجمالي اليوم المسجل</p></div>
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <div class="gh-soft rounded-xl px-2 py-3 text-center"><span class="block text-[10px] text-amber-500">سعرات</span><strong class="mt-1 block text-base tabular-nums">{{ number_format($todayCalories) }}</strong><span class="gh-muted text-[10px]">كالوري</span></div>
                        <div class="gh-soft rounded-xl px-2 py-3 text-center"><span class="block text-[10px] text-emerald-500">بروتين</span><strong class="mt-1 block text-base tabular-nums">{{ number_format($todayProtein, 0) }}</strong><span class="gh-muted text-[10px]">جم</span></div>
                        <div class="gh-soft rounded-xl px-2 py-3 text-center"><span class="block text-[10px] text-blue-500">كارب</span><strong class="mt-1 block text-base tabular-nums">{{ number_format($todayCarbs, 0) }}</strong><span class="gh-muted text-[10px]">جم</span></div>
                    </div>
                    <a href="{{ route('nutrition.index') }}" class="mt-4 inline-block text-[11px] font-bold text-blue-500 hover:underline">تفاصيل الوجبات ←</a>
                </section>
                <section class="gh-card min-w-0 rounded-2xl p-4 md:col-span-2 xl:col-span-4 xl:p-5">
                    <div class="flex items-start justify-between gap-2"><div><h3 class="text-sm font-extrabold">نشاط الأسبوع</h3><p class="gh-muted mt-1 text-[11px]">الجلسات المسجلة آخر 7 أيام</p></div><span class="gh-soft rounded-lg px-2 py-1 text-[11px] font-bold">{{ $weeklyTrainingCount }} / 7</span></div>
                    <div class="mt-5 grid grid-cols-7 gap-1.5" aria-label="أيام التمرين المسجلة">
                        @foreach($weeklyTrainingDays as $day)
                            <div class="flex min-w-0 flex-col items-center gap-2"><div class="flex h-12 w-full max-w-11 items-end overflow-hidden rounded-lg gh-soft"><span class="block w-full rounded-lg {{ $day['trained'] ? 'bg-blue-500' : '' }}" style="height:{{ $day['trained'] ? '100%' : '5%' }}"></span></div><span class="gh-muted truncate text-[10px]" title="{{ $day['date'] }}">{{ $day['label'] }}</span></div>
                        @endforeach
                    </div>
                    <p class="gh-muted mt-3 text-[10px]">{{ $weeklyTrainingCount ? 'الأيام الملونة هي الأيام اللي سجلت فيها جلسة.' : 'ابدأ تسجيل جلساتك علشان يظهر نشاطك.' }}</p>
                </section>
            </div>

            {{-- Bottom row: recent sessions and body-weight chart. --}}
            <div class="grid grid-cols-1 items-stretch gap-4 lg:grid-cols-3">
                <section class="gh-card gh-section-card min-w-0 rounded-2xl p-4 xl:p-5" aria-label="متابعة شرب المياه">
                    <div class="flex items-start justify-between gap-3">
                        <div><h3 class="text-sm font-extrabold">المياه اليوم</h3><p class="gh-muted mt-1 text-[11px]">سجّل اللي شربته خطوة بخطوة</p></div>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-500" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c-2 3-7 8-7 13a7 7 0 0 0 14 0c0-5-5-10-7-13Z"/><path d="M9 17a3 3 0 0 0 3 3"/></svg>
                        </span>
                    </div>
                    <div class="mt-4 flex items-end justify-between gap-3">
                        <div><strong class="text-2xl font-black tabular-nums">{{ number_format($waterConsumedMl / 1000, 2) }}</strong><span class="gh-muted mr-1 text-xs">لتر</span></div>
                        <span class="gh-muted text-[11px]">{{ $waterGoalMl ? 'الهدف '.number_format($waterGoalMl / 1000, 1).' لتر' : 'حدّد هدف شربك اليومي' }}</span>
                    </div>
                    <div class="gh-water-track mt-3 h-2 overflow-hidden rounded-full" role="progressbar" aria-label="المياه المسجلة" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $waterGoalMl ? min(100, round($waterConsumedMl / $waterGoalMl * 100)) : 0 }}">
                        <div class="h-full rounded-full bg-sky-500" style="width: {{ $waterGoalMl ? min(100, round($waterConsumedMl / $waterGoalMl * 100)) : 0 }}%"></div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <form action="{{ route('dashboard.water.add') }}" method="POST">@csrf<input type="hidden" name="amount_ml" value="250"><button type="submit" class="min-h-10 w-full rounded-xl bg-blue-600 px-2 py-2 text-xs font-bold text-white hover:bg-blue-500">+ 250 مل</button></form>
                        <form action="{{ route('dashboard.water.add') }}" method="POST">@csrf<input type="hidden" name="amount_ml" value="500"><button type="submit" class="min-h-10 w-full rounded-xl border border-sky-500/30 bg-sky-500/10 px-2 py-2 text-xs font-bold text-sky-500 hover:bg-sky-500/20">+ 500 مل</button></form>
                    </div>
                    <div class="gh-popover relative mt-4">
                        <details>
                            <summary class="inline-flex cursor-pointer select-none items-center gap-1 text-[11px] font-bold text-blue-500">{{ $waterGoalMl ? 'تعديل هدف المياه' : 'تحديد هدف المياه' }} <span aria-hidden="true">▴</span></summary>
                            <div class="gh-popover-panel">
                                <form action="{{ route('dashboard.water.goal') }}" method="POST" class="flex flex-wrap items-end gap-2">@csrf
                                    <label class="min-w-0 flex-1 text-[11px]">الهدف بالمليلتر<input name="goal_ml" type="number" min="500" max="6000" step="50" required value="{{ $waterGoalMl ?? 2200 }}" class="gh-soft mt-1 w-full rounded-lg border border-blue-500/20 px-2 py-2 text-sm text-inherit" /></label>
                                    <button type="submit" class="min-h-9 rounded-lg bg-blue-600 px-3 text-xs font-bold text-white">حفظ</button>
                                </form>
                                <p class="gh-muted mt-3 text-[10px] leading-5">مرجع تقريبي للمشروبات لدى البالغين: 2.2 لتر للنساء، 3 لتر للرجال. الاحتياج يتغير حسب النشاط والحرارة، ويشمل الماء من الطعام أيضًا في المراجع العلمية.</p>
                            </div>
                        </details>
                    </div>
                    <p class="gh-muted mt-2 text-[10px]">الهدف قابل للتعديل، والتسجيل يبدأ من الصفر كل يوم.</p>
                </section>
                <section class="gh-card gh-section-card min-w-0 rounded-2xl p-4 xl:p-5">
                    <div class="flex items-start justify-between gap-2"><div><h3 class="text-sm font-extrabold">آخر التمارين</h3><p class="gh-muted mt-1 text-[11px]">أحدث الجلسات المسجلة</p></div><a href="{{ route('history.index') }}" class="text-[11px] font-bold text-blue-500 hover:underline">السجل كامل ←</a></div>
                    <div class="mt-4 space-y-2">
                        @forelse($recentSessions as $session)
                            <div class="gh-soft flex items-center justify-between gap-3 rounded-xl px-3 py-3"><div class="flex min-w-0 items-center gap-2"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6v12M18 6v12M3 8v8M21 8v8M6 12h12"/></svg></span><span class="truncate text-xs font-bold">{{ $session->workoutDay?->name ?? 'جلسة تمرين' }}</span></div><span class="gh-muted shrink-0 text-[11px] tabular-nums">{{ \Carbon\Carbon::parse($session->date)->format('d/m') }}</span></div>
                        @empty
                            <div class="gh-chart-empty rounded-xl px-4 py-6 text-center"><p class="text-xs font-bold">لسه مفيش جلسات مسجلة</p><p class="mt-2 text-[11px]">أول جلسة هتظهر هنا بعد تسجيلها.</p></div>
                        @endforelse
                    </div>
                </section>
                <section class="gh-card gh-section-card min-w-0 rounded-2xl p-4 xl:p-5">
                    <div class="flex items-start justify-between gap-2"><div><h3 class="text-sm font-extrabold">تطور وزن الجسم</h3><p class="gh-muted mt-1 text-[11px]">آخر القياسات</p></div><a href="{{ route('measurements.index') }}" class="text-[11px] font-bold text-blue-500 hover:underline">القياسات ←</a></div>
                    @if($hasWeightData)
                        <div class="mt-4 h-40 min-w-0"><canvas id="weightChart" aria-label="منحنى الوزن" role="img"></canvas></div>
                    @else
                        <div class="gh-chart-empty mt-4 flex min-h-40 flex-col items-center justify-center rounded-xl px-4 text-center"><p class="text-xs font-bold">مفيش قياسات وزن لحد دلوقتي</p><a href="{{ route('measurements.index') }}" class="mt-3 text-[11px] font-bold text-blue-500 hover:underline">سجّل أول قياس ←</a></div>
                    @endif
                </section>
            </div>
        </div>

    </div>

    <!-- Chart styles follow the page's current Light / Dark mode. -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chart === 'undefined') return;
            let charts = [];
            const draw = () => {
                charts.forEach(chart => chart.destroy());
                charts = [];
                const light = document.documentElement.dataset.theme === 'light';
                const tick = light ? '#64748b' : '#94a3b8';
                const grid = light ? 'rgba(100,116,139,.12)' : 'rgba(148,163,184,.12)';
                const weight = document.getElementById('weightChart');
                const calories = document.getElementById('nutritionChart');
                const common = {responsive: true,maintainAspectRatio: false,plugins:{legend:{display:false}},
                    scales:{x:{grid:{display:false},ticks:{color:tick,font:{family:'Cairo',size:10},maxRotation:0,autoSkip:true}},
                            y:{beginAtZero:true,grid:{color:grid},ticks:{color:tick,font:{family:'Cairo',size:10}}}}};
                if (weight) {
                    const ctx = weight.getContext('2d');
                    const gradient = ctx.createLinearGradient(0,0,0,250);
                    gradient.addColorStop(0,light?'rgba(37,99,235,.16)':'rgba(56,189,248,.24)');
                    gradient.addColorStop(1,'rgba(37,99,235,0)');
                    charts.push(new Chart(ctx,{type:'line',data:{labels:@json($weightChartLabels),datasets:[{data:@json($weightChartData),borderColor:light?'#2563eb':'#38bdf8',backgroundColor:gradient,fill:true,tension:.34,borderWidth:2.5,pointRadius:3,pointHoverRadius:5,pointBackgroundColor:light?'#2563eb':'#38bdf8'}]},options:{...common,scales:{...common.scales,y:{...common.scales.y,beginAtZero:false}}}}));
                }
                if (calories) {
                    const ctx = calories.getContext('2d');
                    charts.push(new Chart(ctx,{type:'bar',data:{labels:@json($nutritionChartLabels),datasets:[{data:@json($nutritionChartData),backgroundColor:light?'#3b82f6':'#60a5fa',hoverBackgroundColor:light?'#2563eb':'#3b82f6',borderRadius:5,maxBarThickness:25}]},options:common}));
                }
            };
            draw();
            const observer = new MutationObserver(changes => {if(changes.some(change=>change.attributeName==='data-theme')) draw();});
            observer.observe(document.documentElement,{attributes:true,attributeFilter:['data-theme']});
        });
    </script>
</x-app-layout>
