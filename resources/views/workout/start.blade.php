<x-app-layout>
    <style>
        /* Consistent two-field set entry on narrow phones, even before CSS rebuild. */
        .gym-workout-set-row { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem; align-items:center; min-width:0; }
        .gym-workout-set-label { grid-column:1/-1; }
        .gym-workout-set-row input { display:block; width:100%; min-width:0; box-sizing:border-box; }
        @media(min-width:640px) {
            .gym-workout-set-row { grid-template-columns:6.5rem repeat(2,minmax(0,1fr)); gap:.75rem; }
            .gym-workout-set-label { grid-column:auto; }
        }
    </style>
    <x-slot name="header">
        <div class="flex flex-wrap sm:flex-nowrap justify-between items-center gap-3 min-w-0">
            <h2 class="font-bold text-xl sm:text-2xl text-white min-w-0 break-words">
                🏋️‍♂️ جلسة تمرين: {{ $day->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-xs bg-gray-700 text-gray-300 hover:bg-gray-600 px-3 py-2 rounded-xl shrink-0 min-h-11 inline-flex items-center">الرجوع للرئيسية</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        <x-flash-success class="font-bold" />

        @if($day->dayExercises->isEmpty())
            <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 sm:p-10 text-center shadow-xl max-w-xl mx-auto">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-blue-600/10 border border-blue-500/30 flex items-center justify-center text-2xl" aria-hidden="true">🏋️</div>
                <h3 class="text-lg font-extrabold text-white mb-2">لا توجد تمارين مضافة لهذا اليوم</h3>
                <p class="text-sm text-gray-400 leading-7 mb-6">يوم {{ $day->name }} لسه مفيهوش تمارين. أضف التمارين من إدارة الأنظمة وبعدها ارجع لتسجيل جلستك.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('programs.index') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl px-5 py-3 transition">إدارة الأنظمة وإضافة التمارين</a>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center bg-gray-700 hover:bg-gray-600 text-gray-100 font-bold text-sm rounded-xl px-5 py-3 transition">الرجوع للرئيسية</a>
                </div>
            </div>
        @else
        @foreach($day->dayExercises as $dayExercise)
            @php
                $ex = $dayExercise->exercise;
                $last = $previousLogs[$ex->id] ?? null;
                $todayLogs = $savedTodayLogs[$ex->id] ?? collect();
                $isSavedToday = $todayLogs->count() > 0;
            @endphp

            <div class="bg-gray-800 p-4 sm:p-6 rounded-2xl border {{ $isSavedToday ? 'border-green-600/60' : 'border-gray-700' }} shadow-xl space-y-4">
                <form action="{{ route('workout.store-single') }}" method="POST">
                    @csrf
                    <input type="hidden" name="workout_day_id" value="{{ $day->id }}">
                    <input type="hidden" name="exercise_id" value="{{ $ex->id }}">

                    <!-- هيدر التمرين -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-gray-700 pb-3 mb-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 min-w-0">
                                <h3 class="text-lg sm:text-xl font-bold text-white min-w-0 break-words">{{ $ex->name }}</h3>
                                @if($isSavedToday)
                                    <span class="bg-green-950 text-green-400 border border-green-700 text-[10px] px-2 py-0.5 rounded-full font-bold">✓ تم الحفظ اليوم</span>
                                @endif
                            </div>
                            <span class="text-xs bg-blue-950 text-blue-300 border border-blue-800 px-2.5 py-0.5 rounded-md mt-1 inline-block">{{ $ex->target_muscle }}</span>
                        </div>

                        @if($last)
                            <div class="text-xs text-blue-300 bg-blue-950/60 p-2.5 rounded-xl border border-blue-800">
                                📌 آخر أداء سابق: {{ $last->weight }} كجم × {{ $last->reps }} عدات
                            </div>
                        @else
                            <div class="text-xs text-gray-500 italic">أول مرة تسجل هذا التمرين</div>
                        @endif
                    </div>

                    <!-- إدخال المجموعات -->
                    <div class="space-y-3 mb-4">
                        @for($i = 1; $i <= $dayExercise->default_sets; $i++)
                            @php
                                $savedSet = $todayLogs->where('set_number', $i)->first();
                                $wVal = $savedSet ? $savedSet->weight : '';
                                $rVal = $savedSet ? $savedSet->reps : '';
                            @endphp
                            <div class="gym-workout-set-row">
                                <span class="gym-workout-set-label text-sm font-bold text-gray-300">مجموعة {{ $i }}</span>
                                <label class="block min-w-0">
                                    <span class="sr-only">وزن المجموعة {{ $i }} بالكيلوجرام</span>
                                    <input type="number" step="0.5" inputmode="decimal" name="sets[{{ $i-1 }}][weight]" value="{{ $wVal }}" placeholder="الوزن (كجم)" aria-label="وزن المجموعة {{ $i }} بالكيلوجرام" class="w-full min-w-0 bg-gray-900 border-gray-700 text-white placeholder-gray-400 rounded-xl text-sm focus:border-blue-500">
                                </label>
                                <label class="block min-w-0">
                                    <span class="sr-only">عدات المجموعة {{ $i }}</span>
                                    <input type="number" inputmode="numeric" name="sets[{{ $i-1 }}][reps]" value="{{ $rVal }}" placeholder="العدات" aria-label="عدات المجموعة {{ $i }}" class="w-full min-w-0 bg-gray-900 border-gray-700 text-white placeholder-gray-400 rounded-xl text-sm focus:border-blue-500">
                                </label>
                            </div>
                        @endfor
                    </div>

                    <!-- زر حفظ التمرين الحالي منفصلاً -->
                    <div class="flex justify-stretch sm:justify-end border-t border-gray-700/50 pt-3">
                        <x-ui.submit-button class="min-h-11 w-full sm:w-auto py-2 px-4 sm:px-6 text-xs transition shadow-md">
                            💾 حفظ تمرين {{ $ex->name }}
                        </x-ui.submit-button>
                    </div>
                </form>
            </div>
        @endforeach
        @endif

    </div>
</x-app-layout>
