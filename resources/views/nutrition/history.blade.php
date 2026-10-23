<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
                <span>📜</span> سجل السعرات والتغذية التاريخي
            </h2>
            <a href="{{ route('nutrition.index') }}" class="text-xs bg-blue-600 text-white font-bold hover:bg-blue-500 px-4 py-2 rounded-xl transition">
                + تسجيل وجبات اليوم
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">

        <!-- 1. كروت الإحصائيات الشهرية (KPI Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-600/20 border border-amber-500/40 rounded-xl flex items-center justify-center text-2xl">
                    🔥
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">متوسط السعرات اليومية هذا الشهر</p>
                    <h4 class="text-2xl font-black text-amber-400 mt-0.5">{{ number_format($avgDailyCalories) }} <span class="text-xs text-gray-400">كالوري/يوم</span></h4>
                </div>
            </div>

            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-600/20 border border-emerald-500/40 rounded-xl flex items-center justify-center text-2xl">
                    🥩
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">متوسط البروتين اليومي هذا الشهر</p>
                    <h4 class="text-2xl font-black text-emerald-400 mt-0.5">{{ number_format($avgDailyProtein, 1) }} <span class="text-xs text-gray-400">جم/يوم</span></h4>
                </div>
            </div>

            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-600/20 border border-purple-500/40 rounded-xl flex items-center justify-center text-2xl">
                    📅
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">أيام التزام التغذية هذا الشهر</p>
                    <h4 class="text-2xl font-black text-purple-400 mt-0.5">{{ $daysLoggedThisMonth }} <span class="text-xs text-gray-400">أيام</span></h4>
                </div>
            </div>
        </div>

        <!-- 2. شريط الفلترة بيوم محدد -->
        <div class="bg-gray-800 p-5 rounded-2xl border border-gray-700 shadow-xl">
            <form action="{{ route('nutrition.history') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-gray-400 mb-1">📅 اختر اليوم المراد عرض وجباته</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
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
                        <div class="flex items-center gap-3">
                            <span class="text-xs bg-blue-600 text-white font-extrabold px-3 py-1 rounded-xl shadow-md">
                                🗓️ {{ \Carbon\Carbon::parse($dateString)->locale('ar')->translatedFormat('l، d F Y') }}
                            </span>
                            <a href="{{ route('nutrition.index', ['date' => $dateString]) }}" class="text-xs text-blue-400 hover:underline font-bold">
                                ✏️ تعديل وجبات اليوم
                            </a>
                        </div>

                        <!-- الماكروز الإجمالية لليوم -->
                        <div class="flex flex-wrap gap-2 text-xs font-bold">
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
                                    <h4 class="text-xs font-bold text-gray-300 mb-2 flex items-center justify-between">
                                        <span>🍽️ {{ $meal->meal_type }}</span>
                                        <span class="text-[10px] text-gray-400">
                                            🔥 {{ number_format($meal->total_calories) }} كالوري | 🥩 {{ number_format($meal->total_protein, 1) }}ج بروتين
                                        </span>
                                    </h4>

                                    <div class="flex flex-wrap gap-2">
                                        @foreach($meal->items as $item)
                                            <div class="bg-gray-800 border border-gray-700 rounded-lg px-2.5 py-1 text-xs flex items-center gap-1.5 shadow-inner">
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
                <div class="bg-gray-800 p-12 text-center rounded-2xl border border-gray-700 space-y-3">
                    <p class="text-4xl">🥗</p>
                    <h3 class="text-lg font-bold text-white">لا يوجد سجل وجبات مسجلة لهذا التاريخ!</h3>
                    <p class="text-xs text-gray-400">اختر يوماً آخر أو ابدأ بتسجيل وجبات اليوم لتظهر هنا.</p>
                </div>
            @endforelse

            <div class="pt-4">
                {{ $paginatedDates->links() }}
            </div>
        </div>

    </div>
</x-app-layout>
