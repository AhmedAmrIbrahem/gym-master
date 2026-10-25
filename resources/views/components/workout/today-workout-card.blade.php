@props(['activeProgram' => null, 'todayWorkout' => null])

<div id="today-workout-selector" class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700/80 shadow-xl flex flex-col justify-between space-y-3 relative z-20 group scroll-mt-24">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold text-gray-400">🏋️‍♂️ تمرين اليوم</p>
            <h4 class="text-base font-extrabold text-white mt-1">
                {{ $todayWorkout ? 'تم إنجاز الجلسة ✓' : 'لم يُسجل بعد' }}
            </h4>
        </div>
        <div class="w-10 h-10 bg-blue-600/20 border border-blue-500/40 rounded-xl flex items-center justify-center text-xl shrink-0">💪</div>
    </div>

    <div class="pt-2 border-t border-gray-700/60 space-y-2">
        <span class="text-[10px] text-blue-400 font-bold block truncate">
            {{ $activeProgram->name ?? 'لا يوجد نظام نشط' }}
        </span>

        @if($activeProgram && $activeProgram->days->isNotEmpty())
            <details class="group/workout relative">
                <summary class="cursor-pointer list-none bg-blue-600 hover:bg-blue-500 text-center text-white text-[11px] font-bold px-3 py-2 rounded-lg transition shadow-md">
                    ⚡ اختر يوم التمرين <span aria-hidden="true">▾</span>
                </summary>
                <div class="absolute z-50 top-full inset-x-0 mt-2 space-y-1 max-h-48 overflow-y-auto rounded-xl border border-gray-700 bg-gray-800 p-2 shadow-2xl">
                    @foreach($activeProgram->days as $day)
                        <a href="{{ route('workout.start', $day) }}" class="block rounded-lg bg-gray-900/80 hover:bg-gray-700 border border-gray-700 px-3 py-2 text-[11px] font-bold text-white transition">
                            🏋️ {{ $day->name }}
                        </a>
                    @endforeach
                </div>
            </details>
        @elseif($activeProgram)
            <a href="{{ route('programs.index') }}" class="block rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-center text-[11px] font-bold px-3 py-2 transition">أضف أيامًا للنظام أولًا</a>
        @else
            <a href="{{ route('programs.index') }}" class="block rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-center text-[11px] font-bold px-3 py-2 transition">أنشئ أو فعّل نظامًا تدريبيًا</a>
        @endif
    </div>
</div>
