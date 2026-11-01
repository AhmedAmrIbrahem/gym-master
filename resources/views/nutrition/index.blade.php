<x-app-layout>
    <x-slot name="header">
        <div class="gym-nutrition-page-heading flex justify-between items-center gap-3">
            <h2 class="font-extrabold text-2xl text-white flex items-center gap-2 min-w-0">
                <span>🥗</span> تتبع السعرات والوجبات اليومية
            </h2>

            <!-- زر الانتقال للسجل التاريخي -->
            <a href="{{ route('nutrition.history') }}" class="gym-nutrition-history-link flex items-center justify-center gap-2 bg-gray-700 hover:bg-gray-600 text-gray-200 text-xs font-bold px-4 py-2.5 rounded-xl border border-gray-600 transition shadow-md">
                <span>📜</span> السجل التاريخي والإحصائيات
            </a>
        </div>
    </x-slot>

    @php
        $user = auth()->user();
        // جلب نوع اليوم من الـ Request إن وجد، والافتراضي أنه يوم جيم
        $dayType = request('day_type', 'workout');

        if ($dayType === 'rest') {
            $targetCal = $user->rest_target_calories ?? 2000;
            $targetPro = $user->rest_target_protein ?? 160;
            $targetCarb = $user->rest_target_carbs ?? 180;
            $targetFat = $user->rest_target_fats ?? 65;
        } else {
            $targetCal = $user->target_calories ?? 2500;
            $targetPro = $user->target_protein ?? 160;
            $targetCarb = $user->target_carbs ?? 250;
            $targetFat = $user->target_fats ?? 60;
        }

        $calPercent = min(100, round(($totalCalories / max(1, $targetCal)) * 100));
        $proPercent = min(100, round(($totalProtein / max(1, $targetPro)) * 100));
        $carbPercent = min(100, round(($totalCarbs / max(1, $targetCarb)) * 100));
        $fatPercent = min(100, round(($totalFats / max(1, $targetFat)) * 100));

        $remCal = $targetCal - $totalCalories;
        $remPro = $targetPro - $totalProtein;
        $remCarb = $targetCarb - $totalCarbs;
        $remFat = $targetFat - $totalFats;
    @endphp


    <style>
      /* Separate mobile cards from desktop tables; no fragile table-to-card overrides. */
      .gym-meal-picker-row{min-width:0}
      .gym-meal-picker-row > *, .gym-meal-picker-row .gym-picker{min-width:0}
      .gym-mobile-meal-cards{display:none}
      .gym-desktop-meal-table{display:block}
      @media(max-width:639px){
        .gym-meal-picker-row{display:grid!important;grid-template-columns:minmax(0,1fr)!important;gap:10px!important;align-items:stretch!important}
        .gym-meal-picker-row > *, .gym-meal-picker-row .gym-picker,
        .gym-meal-picker-row .gym-search-picker{width:100%!important;max-width:100%!important;min-width:0!important;flex:none!important;flex-grow:0!important;flex-shrink:0!important;height:auto!important;min-height:0!important;align-self:auto!important}
        .gym-meal-picker-row .gym-picker,.gym-meal-picker-row .gym-search-picker{display:block!important}
        .gym-meal-picker-row input[name="grams"],.gym-meal-picker-row button[type="submit"]{height:44px!important;min-height:44px!important;width:100%!important;box-sizing:border-box}
        .gym-desktop-meal-table{display:none!important}
        .gym-mobile-meal-cards{display:grid!important;gap:12px;padding:12px;min-width:0}
        .gym-mobile-meal-card{min-width:0;overflow:hidden;border:1px solid #41516a;border-radius:14px;padding:12px;background:#172335}
        .gym-mobile-meal-delete{appearance:none!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:6px!important;box-sizing:border-box!important;width:auto!important;min-width:76px!important;height:40px!important;min-height:40px!important;padding:0 11px!important;flex-shrink:0!important;border:1px solid rgba(248,113,113,.38)!important;border-radius:10px!important;background:rgba(239,68,68,.10)!important;color:#fca5a5!important;font:700 12px Cairo,sans-serif!important;white-space:nowrap!important;line-height:1!important;cursor:pointer}
        .gym-mobile-meal-delete:focus-visible{outline:2px solid #f87171!important;outline-offset:2px!important}
        .gym-mobile-meal-delete svg{width:16px!important;height:16px!important;flex-shrink:0!important;stroke:currentColor!important}
        html[data-theme="light"] .gym-mobile-meal-delete{background:#fff1f2!important;color:#be123c!important;border-color:#fecdd3!important}
        .gym-mobile-meal-name{min-width:0;overflow-wrap:anywhere;color:#fff;font-size:14px;font-weight:800;line-height:1.7}
        .gym-mobile-meal-stat{min-width:0;border-radius:10px;border:1px solid #33445b;padding:9px 7px;background:#1d2b3d;text-align:center}
        .gym-mobile-meal-stat span{display:block;color:#aebdd0;font-size:10px;margin-bottom:5px}
        .gym-mobile-meal-stat strong{display:block;font-size:12px;overflow-wrap:anywhere}
        html[data-theme="light"] .gym-mobile-meal-card{background:#fff;border-color:#d8e3ef}
        html[data-theme="light"] .gym-mobile-meal-name{color:#1c2c42}
        html[data-theme="light"] .gym-mobile-meal-stat{background:#f5f8fc;border-color:#dce5ef}
        html[data-theme="light"] .gym-mobile-meal-stat span{color:#526784}
      }
      .gym-subtle-scroll{scrollbar-width:thin;scrollbar-color:#53657e transparent}
      .gym-subtle-scroll::-webkit-scrollbar{width:5px;height:5px}
      .gym-subtle-scroll::-webkit-scrollbar-thumb{background:#53657e;border-radius:10px}
    </style>

    <div class="space-y-6">

        <x-flash-success class="font-bold text-sm" />

        <!-- 1. كارت الماكروز مع زر التبديل بين يوم الجيم ويوم الراحة -->
        <x-ui.panel class="p-6 space-y-5">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-700/80 pb-3">
                <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                    🎯 نسبة الالتزام بأهداف اليوم ({{ \Carbon\Carbon::parse($date)->locale('ar')->translatedFormat('l، d F') }})
                </h3>

                <!-- أزرار التبديل بين يوم الجيم ويوم الراحة -->
                <div class="gym-nutrition-day-controls flex items-center gap-3">
                    <div class="flex items-center bg-gray-900 p-1 rounded-xl border border-gray-700 text-xs font-bold">
                        <a href="{{ route('nutrition.index', array_merge(request()->query(), ['day_type' => 'workout'])) }}"
                           class="px-3 py-1.5 rounded-lg transition {{ $dayType === 'workout' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-400 hover:text-white' }}">
                            🏋️‍♂️ يوم جيم
                        </a>
                        <a href="{{ route('nutrition.index', array_merge(request()->query(), ['day_type' => 'rest'])) }}"
                           class="px-3 py-1.5 rounded-lg transition {{ $dayType === 'rest' ? 'bg-purple-600 text-white shadow-md' : 'text-gray-400 hover:text-white' }}">
                            😴 يوم راحة
                        </a>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="text-[11px] text-blue-400 hover:underline font-bold">
                        ⚙️ تعديل الأهداف
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- 1. شريط السعرات -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-amber-400 flex items-center gap-1">🔥 السعرات الحرارية</span>
                        <span class="font-black text-white">
                            {{ number_format($totalCalories) }} / {{ number_format($targetCal) }} كالوري
                            <span class="text-[10px] text-gray-400 font-normal">
                                ({{ $remCal >= 0 ? "متبقي $remCal" : "تجاوزت بـ " . abs($remCal) }})
                            </span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-900 h-3 rounded-full overflow-hidden p-0.5 border border-gray-700">
                        <div class="h-full rounded-full transition-all duration-500 {{ $calPercent >= 100 ? 'bg-red-500' : ($calPercent > 85 ? 'bg-amber-400' : 'bg-amber-500') }}" style="width: {{ $calPercent }}%"></div>
                    </div>
                </div>

                <!-- 2. شريط البروتين -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-emerald-400 flex items-center gap-1">🥩 البروتين</span>
                        <span class="font-black text-white">
                            {{ number_format($totalProtein, 1) }} / {{ number_format($targetPro, 1) }} جم
                            <span class="text-[10px] text-gray-400 font-normal">
                                ({{ $remPro >= 0 ? "متبقي " . number_format($remPro, 1) : "تجاوزت بـ " . number_format(abs($remPro), 1) }})
                            </span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-900 h-3 rounded-full overflow-hidden p-0.5 border border-gray-700">
                        <div class="h-full rounded-full transition-all duration-500 {{ $proPercent >= 100 ? 'bg-emerald-400' : 'bg-emerald-500' }}" style="width: {{ $proPercent }}%"></div>
                    </div>
                </div>

                <!-- 3. شريط الكارب -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-blue-400 flex items-center gap-1">🍞 الكربوهيدرات</span>
                        <span class="font-black text-white">
                            {{ number_format($totalCarbs, 1) }} / {{ number_format($targetCarb, 1) }} جم
                            <span class="text-[10px] text-gray-400 font-normal">
                                ({{ $remCarb >= 0 ? "متبقي " . number_format($remCarb, 1) : "تجاوزت بـ " . number_format(abs($remCarb), 1) }})
                            </span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-900 h-3 rounded-full overflow-hidden p-0.5 border border-gray-700">
                        <div class="h-full rounded-full transition-all duration-500 {{ $carbPercent >= 100 ? 'bg-blue-400' : 'bg-blue-500' }}" style="width: {{ $carbPercent }}%"></div>
                    </div>
                </div>

                <!-- 4. شريط الدهون -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-purple-400 flex items-center gap-1">🥑 الدهون الصحية</span>
                        <span class="font-black text-white">
                            {{ number_format($totalFats, 1) }} / {{ number_format($targetFat, 1) }} جم
                            <span class="text-[10px] text-gray-400 font-normal">
                                ({{ $remFat >= 0 ? "متبقي " . number_format($remFat, 1) : "تجاوزت بـ " . number_format(abs($remFat), 1) }})
                            </span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-900 h-3 rounded-full overflow-hidden p-0.5 border border-gray-700">
                        <div class="h-full rounded-full transition-all duration-500 {{ $fatPercent >= 100 ? 'bg-purple-400' : 'bg-purple-500' }}" style="width: {{ $fatPercent }}%"></div>
                    </div>
                </div>

            </div>
        </x-ui.panel>

        <!-- 2. نموذج إضافة صنف جديد لبنك الأطعمة -->
        <div x-data="{ open: false }" class="bg-gray-800 p-4 rounded-2xl border border-gray-700 shadow-xl">
            <div class="flex justify-between items-center cursor-pointer" @click="open = !open">
                <h3 class="text-sm font-bold text-blue-400 flex items-center gap-2">
                    ➕ إضافة صنف جديد لبنك الأطعمة المخصص
                </h3>
                <span class="text-xs text-gray-400" x-text="open ? '▲ إغلاق' : '▼ فتح'"></span>
            </div>

            <form x-show="open" x-transition action="{{ route('nutrition.storeFood') }}" method="POST" class="mt-4 pt-4 border-t border-gray-700 space-y-4">
                @csrf
                <div class="rounded-xl border border-blue-500/30 bg-blue-500/10 p-3 text-xs text-blue-300 leading-7">
                    <strong>الخطوة 1 — تعريف الصنف:</strong> اكتب بيانات السعرات والماكروز لنفس الكمية الموجودة على العبوة أو اللي حسبتها.
                    لو الأرقام تخص 180 جرام، اكتب 180 في خانة «القيم الغذائية لكم جرام؟».
                    النظام هيحوّلها لكل 100 جرام عند الحفظ تلقائيًا.
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">اسم الطعام</label>
                        <input type="text" name="name" placeholder="مثال: زبادي يوناني" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">القيم الغذائية لكم جرام؟</label>
                        <input type="number" step="0.1" min="0.1" max="100000" name="nutrition_reference_grams" value="{{ old('nutrition_reference_grams', 100) }}" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5" aria-describedby="nutrition-reference-hint">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">السعرات للكمية المحددة</label>
                        <input type="number" step="0.1" name="calories_per_100g" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">بروتين للكمية المحددة</label>
                        <input type="number" step="0.1" name="protein_per_100g" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">كارب للكمية المحددة</label>
                        <input type="number" step="0.1" name="carbs_per_100g" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">دهون للكمية المحددة</label>
                        <input type="number" step="0.1" name="fats_per_100g" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                </div>
                <p id="nutrition-reference-hint" class="text-xs text-gray-400 leading-6">
                    مثال: لو 180 جرام جبنة فيها 306 سعرات، اكتب 180 في خانة المرجع و306 في السعرات. لما تضيف 180 جرام للفطار هيتحسبوا 306 سعرات فقط.
                </p>
                <div class="flex justify-end">
                    <x-ui.submit-button class="px-5 py-2 text-xs shadow-md">
                        حفظ الصنف في البنك
                    </x-ui.submit-button>
                </div>
            </form>
        </div>

        <!-- إدارة أصناف المستخدم فقط: لا يسمح بتغيير الأصناف العامة -->
        <section class="bg-gray-800 rounded-2xl border border-gray-700 p-4 sm:p-5 space-y-4" x-data="{manageOpen: {{ request()->has('bank_q') || request()->has('bank_page') ? 'true' : 'false' }} }">
            <button type="button" @click="manageOpen = !manageOpen" :aria-expanded="manageOpen.toString()" class="flex items-center justify-between gap-3 w-full text-right">
                <span class="font-bold text-blue-400">📚 إدارة أصنافي في بنك الطعام ({{ $ownFoodsCount }})</span>
                <span class="text-xs text-gray-400" x-text="manageOpen ? 'إغلاق −' : 'فتح +'"></span>
            </button>
            <div x-show="manageOpen" x-cloak class="space-y-3">
                <p class="text-xs text-gray-400">تقدر تعدل القيم الغذائية لأي صنف أضفته. الأصناف العامة مش قابلة للتعديل من هنا. البيانات بتتحفظ لكل 100 جرام.</p>
                <form method="GET" action="{{ route('nutrition.index') }}" class="flex flex-col sm:flex-row gap-2">
                    @if(request()->filled('date'))
                        <input type="hidden" name="date" value="{{ request('date') }}">
                    @endif
                    @if(request()->filled('day_type'))
                        <input type="hidden" name="day_type" value="{{ request('day_type') }}">
                    @endif
                    <input type="search" name="bank_q" value="{{ $bankQuery }}" maxlength="80" placeholder="ابحث في كل أصنافك..." aria-label="بحث في أصنافي" class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm" />
                    <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-white text-xs font-bold shrink-0">بحث</button>
                    @if($bankQuery !== '')
                        <a href="{{ route('nutrition.index', request()->except(['bank_q', 'bank_page'])) }}" class="rounded-xl border border-gray-600 px-4 py-2.5 text-center text-gray-300 text-xs font-bold">مسح البحث</a>
                    @endif
                </form>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 max-h-[480px] overflow-y-auto gym-subtle-scroll">
                    @forelse($myFoods as $food)
                        <div class="rounded-xl border border-gray-700 bg-gray-900/50 p-3 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <strong class="text-sm text-white">{{ $food->name }}</strong>
                                <form method="POST" action="{{ route('nutrition.destroyFood', $food) }}" onsubmit="return confirm('حذف الصنف من بنك الطعام؟ لا يمكن التراجع.')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-400 border border-red-500/30 rounded-lg px-3 py-1.5" type="submit">حذف</button>
                                </form>
                            </div>
                            <form method="POST" action="{{ route('nutrition.updateFood', $food) }}" class="space-y-2">
                                @csrf @method('PUT')
                                <label class="block text-xs text-gray-300">اسم الصنف
                                    <input name="name" required maxlength="255" value="{{ $food->name }}" class="mt-1 w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach(['calories_per_100g' => 'سعرات', 'protein_per_100g' => 'بروتين', 'carbs_per_100g' => 'كارب', 'fats_per_100g' => 'دهون'] as $field => $label)
                                        <label class="text-xs text-gray-300">{{ $label }} / 100 جرام
                                            <input type="number" name="{{ $field }}" min="0" step="0.01" required value="{{ $food->{$field} }}" class="mt-1 w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold">حفظ التعديل</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">{{ $bankQuery !== '' ? 'ملقيناش أصناف مطابقة للبحث.' : 'لسه ما أضفتش أصناف خاصة بيك.' }}</p>
                    @endforelse
                </div>
                @if($myFoods->hasPages())
                    <nav aria-label="صفحات بنك الطعام" class="flex items-center justify-between gap-2 pt-2 text-xs font-bold">
                        @if($myFoods->onFirstPage())
                            <span class="rounded-lg border border-gray-700 px-3 py-2 text-gray-500">السابق</span>
                        @else
                            <a class="rounded-lg border border-blue-500/30 px-3 py-2 text-blue-400" href="{{ $myFoods->previousPageUrl() }}">السابق</a>
                        @endif
                        <span class="text-gray-400">صفحة {{ $myFoods->currentPage() }} من {{ $myFoods->lastPage() }}</span>
                        @if($myFoods->hasMorePages())
                            <a class="rounded-lg border border-blue-500/30 px-3 py-2 text-blue-400" href="{{ $myFoods->nextPageUrl() }}">التالي</a>
                        @else
                            <span class="rounded-lg border border-gray-700 px-3 py-2 text-gray-500">التالي</span>
                        @endif
                    </nav>
                @endif
            </div>
        </section>

        <!-- 3. كروت الوجبات اليومية -->
        <div class="space-y-6">
            @foreach($dailyMeals as $mealType => $meal)
                <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
                    <div class="p-4 bg-gray-900/80 border-b border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            🍽️ {{ $mealType }}
                        </h3>
                        <div class="flex gap-3 text-xs font-semibold text-gray-300">
                            <span class="text-amber-400">🔥 {{ number_format($meal->total_calories) }} كالوري</span>
                            <span class="text-emerald-400">🥩 {{ number_format($meal->total_protein, 1) }}ج بروتين</span>
                        </div>
                    </div>

                    <!-- نموذج إضافة طعام للوجبة -->
                    <div class="p-4 bg-gray-900/40 border-b border-gray-700/50">
                        <form action="{{ route('nutrition.storeMealItem') }}" method="POST" class="gym-meal-picker-row flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            @csrf
                            <input type="hidden" name="daily_meal_id" value="{{ $meal->id }}">

                            <select name="food_id" data-searchable-select data-food-search-url="{{ route('nutrition.searchFoods') }}" required class="w-full sm:w-1/2 bg-gray-900 border-gray-700 text-white text-xs rounded-xl p-2.5">
                                <option value="">اختر الطعام من البنك...</option>
                                @foreach($foods as $f)
                                    <option value="{{ $f->id }}" @selected((string) old('food_id') === (string) $f->id)>
                                        {{ $f->name }} ({{ $f->calories_per_100g }} كالوري / {{ $f->protein_per_100g }}ج بروتين لكل 100ج)
                                    </option>
                                @endforeach
                                @if($selectedFood && !$foods->contains('id', $selectedFood->id))
                                    <option value="{{ $selectedFood->id }}" selected>
                                        {{ $selectedFood->name }} ({{ $selectedFood->calories_per_100g }} كالوري / {{ $selectedFood->protein_per_100g }}ج بروتين لكل 100ج)
                                    </option>
                                @endif
                            </select>

                            <input type="number" step="0.1" min="0.1" name="grams" placeholder="الكمية التي أكلتها بالجرام (مثال: 180)" required class="w-full sm:w-1/3 bg-gray-900 border-gray-700 text-white text-xs rounded-xl p-2.5">

                            <button type="submit" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-5 py-2.5 rounded-xl text-xs transition shrink-0">
                                + إضافة للوجبة
                            </button>
                        </form>
                        <p class="mt-2 text-[11px] text-gray-400 leading-6">الخطوة 2 — اختار الصنف ثم أدخل وزن الكمية اللي أكلتها فقط. السعرات هتتحسب تلقائيًا حسب الكمية.</p>
                    </div>

                    <!-- Desktop: normal table; mobile: independent cards (never CSS-converted table cells). -->
                    <div class="gym-desktop-meal-table overflow-x-auto">
                        <table class="w-full text-right text-xs text-gray-300">
                            <thead class="bg-gray-900/30 text-gray-400">
                                <tr>
                                    <th class="p-3">الصنف</th><th class="p-3">الكمية</th><th class="p-3">السعرات</th>
                                    <th class="p-3">البروتين</th><th class="p-3">الكارب</th><th class="p-3">الدهون</th>
                                    <th class="p-3 text-center">حذف</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/40">
                                @forelse($meal->items as $item)
                                    <tr class="hover:bg-gray-700/20">
                                        <td class="p-3 font-bold text-white">{{ $item->food->name }}</td>
                                        <td class="p-3 font-bold text-blue-400">{{ $item->grams }} جرام</td>
                                        <td class="p-3 font-bold text-amber-400">{{ $item->calories }} كالوري</td>
                                        <td class="p-3 text-emerald-400 font-bold">{{ $item->protein }}ج</td>
                                        <td class="p-3 text-blue-300">{{ $item->carbs }}ج</td>
                                        <td class="p-3 text-purple-300">{{ $item->fats }}ج</td>
                                        <td class="p-3 text-center">
                                            <form action="{{ route('nutrition.destroyMealItem', $item->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 font-bold" aria-label="حذف {{ $item->food->name }} من الوجبة">✕</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="p-4 text-center text-gray-500 italic">لم يتم إضافة أطعمة لهذه الوجبة بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="gym-mobile-meal-cards" aria-label="الأطعمة المسجلة في {{ $mealType }}">
                        @forelse($meal->items as $item)
                            <article class="gym-mobile-meal-card">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <strong class="gym-mobile-meal-name">{{ $item->food->name }}</strong>
                                    <form action="{{ route('nutrition.destroyMealItem', $item->id) }}" method="POST" class="shrink-0" onsubmit="return confirm('حذف {{ $item->food->name }} من الوجبة؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="gym-mobile-meal-delete" aria-label="حذف {{ $item->food->name }} من الوجبة"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m5 4v7m4-7v7"/></svg><span>حذف</span></button>
                                    </form>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="gym-mobile-meal-stat"><span>الكمية</span><strong class="text-blue-400">{{ $item->grams }} جرام</strong></div>
                                    <div class="gym-mobile-meal-stat"><span>السعرات</span><strong class="text-amber-400">{{ $item->calories }} كالوري</strong></div>
                                    <div class="gym-mobile-meal-stat"><span>البروتين</span><strong class="text-emerald-400">{{ $item->protein }} جرام</strong></div>
                                    <div class="gym-mobile-meal-stat"><span>الكارب</span><strong class="text-blue-400">{{ $item->carbs }} جرام</strong></div>
                                    <div class="gym-mobile-meal-stat"><span>الدهون</span><strong class="text-purple-400">{{ $item->fats }} جرام</strong></div>
                                </div>
                            </article>
                        @empty
                            <p class="text-center text-sm text-gray-400 py-4">لسه ما أضفتش أطعمة للوجبة دي.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-app-layout>
