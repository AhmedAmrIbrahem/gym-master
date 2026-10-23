<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-white">
                🏋️‍♂️ جلسة تمرين: {{ $day->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-xs bg-gray-700 text-gray-300 hover:bg-gray-600 px-3 py-2 rounded-xl">الرجوع للرئيسية</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        <x-flash-success class="font-bold" />

        @foreach($day->dayExercises as $dayExercise)
            @php
                $ex = $dayExercise->exercise;
                $last = $previousLogs[$ex->id] ?? null;
                $todayLogs = $savedTodayLogs[$ex->id] ?? collect();
                $isSavedToday = $todayLogs->count() > 0;
            @endphp

            <div class="bg-gray-800 p-6 rounded-2xl border {{ $isSavedToday ? 'border-green-600/60' : 'border-gray-700' }} shadow-xl space-y-4">
                <form action="{{ route('workout.store-single') }}" method="POST">
                    @csrf
                    <input type="hidden" name="workout_day_id" value="{{ $day->id }}">
                    <input type="hidden" name="exercise_id" value="{{ $ex->id }}">

                    <!-- هيدر التمرين -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-gray-700 pb-3 mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-bold text-white">{{ $ex->name }}</h3>
                                @if($isSavedToday)
                                    <span class="bg-green-950 text-green-400 border border-green-700 text-[10px] px-2 py-0.5 rounded-full font-bold">✓ تم الحفظ اليوم</span>
                                @endif
                            </div>
                            <span class="text-xs bg-blue-950 text-blue-300 border border-blue-800 px-2.5 py-0.5 rounded-md mt-1 inline-block">{{ $ex->target_muscle }}</span>
                        </div>

                        @if($last)
                            <div class="text-xs text-blue-300 bg-blue-950/60 p-2.5 rounded-xl border border-blue-800">
                                📌 **آخر أدآء سابق:** {{ $last->weight }} كجم × {{ $last->reps }} عدات
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
                            <div class="flex items-center gap-3">
                                <span class="w-24 text-sm font-bold text-gray-400">مجموعة {{ $i }}</span>
                                <input type="number" step="0.5" name="sets[{{ $i-1 }}][weight]" value="{{ $wVal }}" placeholder="الوزن (كجم)" class="w-full bg-gray-900 border-gray-700 text-white placeholder-gray-600 rounded-xl text-sm focus:border-blue-500">
                                <input type="number" name="sets[{{ $i-1 }}][reps]" value="{{ $rVal }}" placeholder="العدات" class="w-full bg-gray-900 border-gray-700 text-white placeholder-gray-600 rounded-xl text-sm focus:border-blue-500">
                            </div>
                        @endfor
                    </div>

                    <!-- زر حفظ التمرين الحالي منفصلاً -->
                    <div class="flex justify-end border-t border-gray-700/50 pt-3">
                        <x-ui.submit-button class="py-2 px-6 text-xs transition shadow-md">
                            💾 حفظ تمرين {{ $ex->name }}
                        </x-ui.submit-button>
                    </div>
                </form>
            </div>
        @endforeach

    </div>
</x-app-layout>
