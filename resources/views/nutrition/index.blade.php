<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
                <span>🥗</span> تتبع السعرات والوجبات اليومية
            </h2>

            <!-- زر الانتقال للسجل التاريخي -->
            <a href="{{ route('nutrition.history') }}" class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-gray-200 text-xs font-bold px-4 py-2.5 rounded-xl border border-gray-600 transition shadow-md">
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

    <div class="space-y-6">

        <x-flash-success class="font-bold text-sm" />

        <!-- 1. كارت الماكروز مع زر التبديل بين يوم الجيم ويوم الراحة -->
        <x-ui.panel class="p-6 space-y-5">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-700/80 pb-3">
                <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                    🎯 نسبة الالتزام بأهداف اليوم ({{ \Carbon\Carbon::parse($date)->locale('ar')->translatedFormat('l، d F') }})
                </h3>

                <!-- أزرار التبديل بين يوم الجيم ويوم الراحة -->
                <div class="flex items-center gap-3">
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
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">اسم الطعام</label>
                        <input type="text" name="name" placeholder="مثال: زبادي يوناني" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">سعرات / 100ج</label>
                        <input type="number" step="0.1" name="calories_per_100g" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">بروتين / 100ج</label>
                        <input type="number" step="0.1" name="protein_per_100g" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">كارب / 100ج</label>
                        <input type="number" step="0.1" name="carbs_per_100g" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">دهون / 100ج</label>
                        <input type="number" step="0.1" name="fats_per_100g" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-xs p-2.5">
                    </div>
                </div>
                <div class="flex justify-end">
                    <x-ui.submit-button class="px-5 py-2 text-xs shadow-md">
                        حفظ الصنف في البنك
                    </x-ui.submit-button>
                </div>
            </form>
        </div>

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
                        <form action="{{ route('nutrition.storeMealItem') }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3">
                            @csrf
                            <input type="hidden" name="daily_meal_id" value="{{ $meal->id }}">

                            <select name="food_id" required class="w-full sm:w-1/2 bg-gray-900 border-gray-700 text-white text-xs rounded-xl p-2.5">
                                <option value="">اختر الطعام من البنك...</option>
                                @foreach($foods as $f)
                                    <option value="{{ $f->id }}">
                                        {{ $f->name }} ({{ $f->calories_per_100g }} كالوري / {{ $f->protein_per_100g }}ج بروتين لكل 100ج)
                                    </option>
                                @endforeach
                            </select>

                            <input type="number" step="1" name="grams" placeholder="الكمية بالجرام (مثال: 150)" required class="w-full sm:w-1/3 bg-gray-900 border-gray-700 text-white text-xs rounded-xl p-2.5">

                            <button type="submit" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-5 py-2.5 rounded-xl text-xs transition shrink-0">
                                + إضافة للوجبة
                            </button>
                        </form>
                    </div>

                    <!-- الأصناف المضافة للوجبة -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs text-gray-300">
                            <thead class="bg-gray-900/30 text-gray-400">
                            <tr>
                                <th class="p-3">الصنف</th>
                                <th class="p-3">الكمية</th>
                                <th class="p-3">السعرات</th>
                                <th class="p-3">البروتين</th>
                                <th class="p-3">الكارب</th>
                                <th class="p-3">الدهون</th>
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
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 font-bold">✕</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-gray-500 italic">
                                        لم يتم إضافة أطعمة لهذه الوجبة بعد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-app-layout>
