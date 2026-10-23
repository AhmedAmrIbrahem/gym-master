<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
                <span>📜</span> سجل التمارين والتاريخ
            </h2>
            <span class="text-xs bg-blue-950 text-blue-400 border border-blue-800 px-3 py-1.5 rounded-xl font-bold">
                تحليل الأداء والتطور
            </span>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- 1. كروت الإحصائيات السريعة (KPI Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-600/20 border border-blue-500/40 rounded-xl flex items-center justify-center text-2xl">
                    🏋️‍♂️
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">إجمالي التمرينات الكلي</p>
                    <h4 class="text-2xl font-black text-white mt-0.5">{{ number_format($totalSessions) }}</h4>
                </div>
            </div>

            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-600/20 border border-emerald-500/40 rounded-xl flex items-center justify-center text-2xl">
                    ⚡
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">إجمالي المجموعات (Total Sets)</p>
                    <h4 class="text-2xl font-black text-emerald-400 mt-0.5">{{ number_format($totalLogs) }}</h4>
                </div>
            </div>

            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-600/20 border border-purple-500/40 rounded-xl flex items-center justify-center text-2xl">
                    📆
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">أيام التمرين هذا الشهر</p>
                    <h4 class="text-2xl font-black text-purple-400 mt-0.5">{{ $monthlyDaysCount }} <span class="text-xs font-bold text-gray-400">أيام</span></h4>
                </div>
            </div>
        </div>

        <!-- 2. شريط الفلترة المتقدم (Filter Bar) -->
        <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl">
            <form action="{{ route('history.index') }}" method="GET" class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-700/60 pb-3">
                    <h3 class="text-sm font-bold text-blue-400 flex items-center gap-2">
                        🔍 تصفية وتحديد السجل
                    </h3>
                    @if(request()->anyFilled(['workout_day_id', 'exercise_id', 'date_from', 'date_to', 'search']))
                        <a href="{{ route('history.index') }}" class="text-xs text-red-400 hover:underline">
                            ✕ إزالة كل الفلاتر
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- اختر اليوم -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">اليوم / التمرينة</label>
                        <select name="workout_day_id" class="w-full bg-gray-900 border-gray-700 text-white text-xs rounded-xl focus:border-blue-500">
                            <option value="">جميع الأيام...</option>
                            @foreach($workoutDays as $d)
                                <option value="{{ $d->id }}" {{ request('workout_day_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- اختر التمرين -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">تمرين معين</label>
                        <select name="exercise_id" class="w-full bg-gray-900 border-gray-700 text-white text-xs rounded-xl focus:border-blue-500">
                            <option value="">جميع التمارين...</option>
                            @foreach($exercises as $ex)
                                <option value="{{ $ex->id }}" {{ request('exercise_id') == $ex->id ? 'selected' : '' }}>
                                    {{ $ex->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- من تاريخ -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-gray-900 border-gray-700 text-white text-xs rounded-xl focus:border-blue-500">
                    </div>

                    <!-- إلى تاريخ -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">إلى تاريخ</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-gray-900 border-gray-700 text-white text-xs rounded-xl focus:border-blue-500">
                    </div>

                    <!-- بحث نصي -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">كلمة بحث</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="اسم تمرين أو ملاحظة..." class="w-full bg-gray-900 border-gray-700 text-white text-xs rounded-xl focus:border-blue-500">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <x-ui.submit-button class="px-6 py-2 text-xs transition shadow-lg flex items-center gap-1.5">
                        <span>🔎</span> تطبيق الفلتر
                    </x-ui.submit-button>
                </div>
            </form>
        </div>

        <!-- 3. عرض الجلسات بشكل احترافي (Timeline Style) -->
        <div class="space-y-4">
            @forelse($sessions as $session)
                <div class="bg-gray-800 rounded-2xl border border-gray-700/80 shadow-xl overflow-hidden hover:border-gray-600 transition">

                    <!-- Header الجلسة -->
                    <div class="bg-gray-900/80 p-4 border-b border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                        <div class="flex items-center gap-3">
                            <span class="text-xs bg-blue-600 text-white font-extrabold px-3 py-1 rounded-xl shadow-md">
                                {{ $session->workoutDay->name ?? 'جلسة تمرين' }}
                            </span>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                📅 {{ \Carbon\Carbon::parse($session->created_at)->locale('ar')->translatedFormat('l، d F Y') }}
                            </h3>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="bg-gray-800 text-gray-300 border border-gray-700 px-3 py-1 rounded-xl">
                                ⏰ {{ \Carbon\Carbon::parse($session->created_at)->format('h:i A') }}
                            </span>
                        </div>
                    </div>

                    <!-- جدول تفاصيل التمارين والمجموعات -->
                    <div class="p-4 overflow-x-auto">
                        <table class="w-full text-right text-xs text-gray-300">
                            <thead class="bg-gray-900/40 text-gray-400 uppercase">
                            <tr>
                                <th class="p-3 rounded-r-xl">التمرين</th>
                                <th class="p-3">العضلة</th>
                                <th class="p-3 rounded-l-xl">المجموعات والأوزان</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/40">
                            @php
                                $groupedLogs = $session->logs->groupBy('exercise_id');
                            @endphp

                            @foreach($groupedLogs as $exerciseId => $logs)
                                @php $exercise = $logs->first()->exercise; @endphp
                                <tr class="hover:bg-gray-700/30 transition">
                                    <td class="p-3 font-bold text-white text-sm">{{ $exercise->name ?? 'تمرين' }}</td>
                                    <td class="p-3">
                                            <span class="bg-blue-950/60 text-blue-300 border border-blue-800 px-2.5 py-0.5 rounded-md">
                                                {{ $exercise->target_muscle ?? '-' }}
                                            </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($logs as $log)
                                                <div class="bg-gray-900 border border-gray-700/80 rounded-xl px-3 py-1.5 flex items-center gap-2 shadow-inner">
                                                    <span class="text-[10px] text-gray-400 font-bold">م{{ $log->set_number }}</span>
                                                    <span class="text-emerald-400 font-black text-sm">{{ $log->weight }} <span class="text-[10px] text-gray-400">كجم</span></span>
                                                    <span class="text-gray-500 text-[10px]">×</span>
                                                    <span class="text-white font-bold">{{ $log->reps }} <span class="text-[10px] text-gray-400">عدات</span></span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($session->notes)
                        <div class="bg-gray-900/90 p-3 border-t border-gray-700/60 text-xs text-gray-300 flex items-center gap-2">
                            <span>💬</span> <b>ملاحظة:</b> {{ $session->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-gray-800 p-12 text-center rounded-2xl border border-gray-700 space-y-3">
                    <p class="text-4xl">🔍</p>
                    <h3 class="text-lg font-bold text-white">لا توجد نتائج مطابقة لمحدادت الفلترة!</h3>
                    <p class="text-xs text-gray-400">جرب البحث بكلمات أخرى أو اختر تاريخ مختلف.</p>
                </div>
            @endforelse

            <div class="pt-4">
                {{ $sessions->links() }}
            </div>
        </div>

    </div>
</x-app-layout>
