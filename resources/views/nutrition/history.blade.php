<x-app-layout>
    <style>
        .nutrition-history-header,.nutrition-history-page{--nh-text:#f1f5f9;--nh-muted:#a5b4c8;--nh-surface:#1f2937;--nh-line:#374151;min-width:0}
        .nutrition-history-header{display:flex;align-items:center;justify-content:space-between;gap:16px;width:100%}
        .nutrition-history-title{margin:0;color:var(--nh-text);font-size:24px;font-weight:800;line-height:1.6}
        .nutrition-history-subtitle{margin:4px 0 0;font-size:12px;line-height:1.8;color:var(--nh-muted)}
        .nutrition-history-add{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:44px;padding:10px 16px;border:1px solid #3b82f6;border-radius:12px;background:#2563eb;color:white;font-size:12px;font-weight:800;white-space:nowrap;flex-shrink:0}
        .nutrition-history-add:hover{background:#1d4ed8}
        .nutrition-history-add:focus-visible{outline:3px solid #93c5fd;outline-offset:3px}
        .nutrition-history-page>*+*{margin-top:20px}
        .nutrition-history-month{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;margin-bottom:12px}
        .nutrition-history-month h3{font-size:13px;font-weight:800;color:var(--nh-text)}
        .nutrition-history-month span{font-size:11px;color:var(--nh-muted)}
        .nutrition-history-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
        .nutrition-history-metric{display:flex;align-items:center;gap:14px;min-width:0;padding:20px;border:1px solid var(--nh-line);border-radius:17px;background:var(--nh-surface);box-shadow:0 4px 14px rgba(0,0,0,.04)}
        .nutrition-history-symbol{display:grid;place-items:center;flex-shrink:0;width:44px;height:44px;border-radius:13px;font-size:23px;background:rgba(148,163,184,.09)}
        .nutrition-history-metric-copy{min-width:0}
        .nutrition-history-metric p{margin:0;font-size:12px;line-height:1.7;color:var(--nh-muted)}
        .nutrition-history-metric strong{display:block;font-size:27px;font-weight:900;line-height:1.4;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
        .nutrition-history-metric small{display:block;font-size:10px;line-height:1.5;color:var(--nh-muted)}
        .nutrition-history-metric.is-calories strong{color:#fbbf24}.nutrition-history-metric.is-protein strong{color:#34d399}.nutrition-history-metric.is-days strong{color:#c4b5fd}
        .nutrition-history-filter{padding:18px!important}
        .nutrition-history-filter form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;align-items:end}
        .nutrition-history-filter-field{min-width:0;width:100%}
        .nutrition-history-filter label{display:block;margin-bottom:8px;font-size:12px;font-weight:700;color:var(--nh-muted)}
        .nutrition-history-filter input[type="date"]{display:block;width:100%;max-width:100%;min-width:0;height:46px;box-sizing:border-box;font-size:16px;border-radius:12px;appearance:none;-webkit-appearance:none}
        .nutrition-history-filter input[type="date"]::-webkit-date-and-time-value{min-height:1.4em;text-align:start}
        .nutrition-history-filter-actions{display:flex;align-items:center;gap:8px;min-width:0}
        .nutrition-history-filter-actions button,.nutrition-history-filter-actions a{min-height:46px;justify-content:center;font-size:12px}
        .nutrition-history-empty{padding:28px 18px}
        .nutrition-history-food{min-width:0;max-width:100%;flex-wrap:wrap;overflow-wrap:anywhere}
        html[data-theme="light"] .nutrition-history-header,html[data-theme="light"] .nutrition-history-page{--nh-text:#172033;--nh-muted:#64748b;--nh-surface:#fff;--nh-line:#dbe3ef}
        html[data-theme="light"] .nutrition-history-metric.is-calories strong{color:#b45309}html[data-theme="light"] .nutrition-history-metric.is-protein strong{color:#047857}html[data-theme="light"] .nutrition-history-metric.is-days strong{color:#6d28d9}
        @media(max-width:767px){
            .nutrition-history-header{flex-direction:column;align-items:stretch;gap:10px}
            .nutrition-history-title{font-size:19px}.nutrition-history-subtitle{font-size:11px}
            .nutrition-history-add{width:100%;min-height:42px;white-space:normal;text-align:center}
            .nutrition-history-metrics{gap:8px}
            .nutrition-history-metric{flex-direction:column;align-items:flex-start;gap:8px;padding:12px 10px;border-radius:14px}
            .nutrition-history-symbol{width:30px;height:30px;font-size:18px;border-radius:9px}
            .nutrition-history-metric p{font-size:11px;min-height:38px}
            .nutrition-history-metric strong{font-size:23px}.nutrition-history-metric small{font-size:10px}
            .nutrition-history-filter{padding:15px!important}.nutrition-history-filter form{grid-template-columns:minmax(0,1fr)}
            .nutrition-history-filter-actions{padding-inline-end:54px;flex-wrap:wrap}
            .nutrition-history-filter-actions button,.nutrition-history-filter-actions a{flex:1 1 110px;width:auto!important;padding-inline:10px}
            .nutrition-history-date{flex-wrap:wrap;gap:8px;width:100%;min-width:0}
            .nutrition-history-date>span{white-space:normal;line-height:1.9}
            .nutrition-history-totals{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));width:100%;gap:6px}
            .nutrition-history-totals>span{min-width:0;text-align:center;font-size:11px;overflow-wrap:anywhere}
            .nutrition-history-meal-title{flex-direction:column;align-items:flex-start;gap:6px;line-height:1.8}
            .nutrition-history-empty h3{font-size:16px;line-height:1.8}
            .nutrition-history-pagination{padding-inline-end:54px}
        }
    </style>
    <x-slot name="header">
        <div class="nutrition-history-header">
            <div>
                <h2 class="nutrition-history-title">📜 سجل السعرات والتغذية</h2>
                <p class="nutrition-history-subtitle">راجع وجباتك السابقة، واختَر اليوم اللي تحب تشوفه.</p>
            </div>
            <a href="{{ route('nutrition.index') }}" class="nutrition-history-add">
                <span aria-hidden="true">+</span> تسجيل وجبات اليوم
            </a>
        </div>
    </x-slot>

    <div class="nutrition-history-page">

        <!-- 1. كروت الإحصائيات الشهرية (KPI Cards) -->
        <section aria-labelledby="nutrition-history-month-title">
            <div class="nutrition-history-month"><h3 id="nutrition-history-month-title">ملخص الشهر</h3><span>{{ now()->locale('ar')->translatedFormat('F Y') }}</span></div>
            <div class="nutrition-history-metrics">
                <div class="nutrition-history-metric is-calories">
                    <span class="nutrition-history-symbol" aria-hidden="true">🔥</span>
                    <div class="nutrition-history-metric-copy"><p>متوسط السعرات</p><strong>{{ number_format($avgDailyCalories) }}</strong><small>كالوري / يوم</small></div>
                </div>
                <div class="nutrition-history-metric is-protein">
                    <span class="nutrition-history-symbol" aria-hidden="true">🥩</span>
                    <div class="nutrition-history-metric-copy"><p>متوسط البروتين</p><strong>{{ number_format($avgDailyProtein, 1) }}</strong><small>جم / يوم</small></div>
                </div>
                <div class="nutrition-history-metric is-days">
                    <span class="nutrition-history-symbol" aria-hidden="true">📅</span>
                    <div class="nutrition-history-metric-copy"><p>أيام التسجيل</p><strong>{{ $daysLoggedThisMonth }}</strong><small>يوم هذا الشهر</small></div>
                </div>
            </div>
        </section>

        <!-- 2. شريط الفلترة بيوم محدد -->
        <div class="nutrition-history-filter bg-gray-800 rounded-2xl border border-gray-700 shadow-xl">
            <form action="{{ route('nutrition.history') }}" method="GET">
                <div class="nutrition-history-filter-field">
                    <label for="nutrition-history-date">📅 اختر يومًا لعرض وجباته</label>
                    <input id="nutrition-history-date" type="date" name="date" value="{{ request('date') }}" class="bg-gray-900 border-gray-700 text-white p-2.5 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="nutrition-history-filter-actions">
                    <x-ui.submit-button class="px-6 py-2.5 text-xs transition shadow-lg w-full sm:w-auto flex items-center justify-center gap-1">
                        🔎 بحث باليوم
                    </x-ui.submit-button>
                    @if(request()->filled('date'))
                        <a href="{{ route('nutrition.history') }}" class="bg-gray-700 hover:bg-gray-600 text-gray-300 font-bold px-4 py-2.5 rounded-xl text-xs transition flex items-center justify-center gap-1">
                            ✕ عرض جميع الأيام
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- 3. قائمة الأيام والوجبات التاريخية -->
        <div class="space-y-6">
            @forelse($paginatedDates as $dateString => $dayMeals)
                @php
                    $dayCalories = 0;
                    $dayProtein = 0;
                    $dayCarbs = 0;
                    $dayFats = 0;

                    foreach($dayMeals as $m) {
                        $dayCalories += $m->total_calories;
                        $dayProtein += $m->total_protein;
                        $dayCarbs += $m->total_carbs;
                        $dayFats += $m->total_fats;
                    }
                @endphp

                <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
                    <!-- هيدر اليوم والمجاميع الكلية -->
                    <div class="bg-gray-900/90 p-4 border-b border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                        <div class="nutrition-history-date flex items-center gap-3">
                            <span class="text-xs bg-blue-600 text-white font-extrabold px-3 py-1 rounded-xl shadow-md">
                                🗓️ {{ \Carbon\Carbon::parse($dateString)->locale('ar')->translatedFormat('l، d F Y') }}
                            </span>
                            <a href="{{ route('nutrition.index', ['date' => $dateString]) }}" class="text-xs text-blue-400 hover:underline font-bold">
                                ✏️ تعديل وجبات اليوم
                            </a>
                        </div>

                        <!-- الماكروز الإجمالية لليوم -->
                        <div class="nutrition-history-totals flex flex-wrap gap-2 text-xs font-bold">
                            <span class="bg-gray-800 border border-amber-500/40 text-amber-400 px-2.5 py-1 rounded-lg">🔥 {{ number_format($dayCalories) }} كالوري</span>
                            <span class="bg-gray-800 border border-emerald-500/40 text-emerald-400 px-2.5 py-1 rounded-lg">🥩 {{ number_format($dayProtein, 1) }}ج بروتين</span>
                            <span class="bg-gray-800 border border-blue-500/40 text-blue-300 px-2.5 py-1 rounded-lg">🍞 {{ number_format($dayCarbs, 1) }}ج كارب</span>
                            <span class="bg-gray-800 border border-purple-500/40 text-purple-300 px-2.5 py-1 rounded-lg">🥑 {{ number_format($dayFats, 1) }}ج دهون</span>
                        </div>
                    </div>

                    <!-- تفاصيل الوجبات لكل يوم -->
                    <div class="p-4 space-y-4">
                        @foreach($dayMeals as $meal)
                            @if($meal->items->count() > 0)
                                <div class="bg-gray-900/40 border border-gray-700/60 rounded-xl p-3">
                                    <h4 class="nutrition-history-meal-title text-xs font-bold text-gray-300 mb-2 flex items-center justify-between">
                                        <span>🍽️ {{ $meal->meal_type }}</span>
                                        <span class="text-[10px] text-gray-400">
                                            🔥 {{ number_format($meal->total_calories) }} كالوري | 🥩 {{ number_format($meal->total_protein, 1) }}ج بروتين
                                        </span>
                                    </h4>

                                    <div class="flex flex-wrap gap-2">
                                        @foreach($meal->items as $item)
                                            <div class="nutrition-history-food bg-gray-800 border border-gray-700 rounded-lg px-2.5 py-1 text-xs flex items-center gap-1.5 shadow-inner">
                                                <span class="font-bold text-white">{{ $item->food->name }}</span>
                                                <span class="text-blue-400 text-[10px]">({{ $item->grams }}ج)</span>
                                                <span class="text-gray-500">|</span>
                                                <span class="text-amber-400 font-bold text-[10px]">{{ $item->calories }}كالوري</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="nutrition-history-empty bg-gray-800 text-center rounded-2xl border border-gray-700 space-y-3">
                    <p class="text-4xl">🥗</p>
                    <h3 class="text-lg font-bold text-white">لا يوجد سجل وجبات مسجلة لهذا التاريخ!</h3>
                    <p class="text-xs text-gray-400">اختر يوماً آخر أو ابدأ بتسجيل وجبات اليوم لتظهر هنا.</p>
                </div>
            @endforelse

            <div class="nutrition-history-pagination pt-4">
                {{ $paginatedDates->links() }}
            </div>
        </div>

    </div>
</x-app-layout>
