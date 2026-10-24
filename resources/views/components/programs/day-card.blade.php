@props(['day', 'groupedExercises'])

<div class="gym-day-card bg-gray-900 p-4 rounded-xl border border-gray-700 space-y-3 flex flex-col justify-between" x-data="{ editDay: false }">
    <div>
        <!-- هيدر اليوم (تعديل + حذف) -->
        <div class="flex justify-between items-center border-b border-gray-800 pb-2 mb-3">
            <template x-if="!editDay">
                <div class="flex items-center gap-2">
                    <h4 class="font-bold text-lg text-blue-400">{{ $day->name }}</h4>
                    <button @click="editDay = true" class="text-[10px] text-gray-400 hover:text-blue-400">✏️</button>
                </div>
            </template>

            <template x-if="editDay">
                <form action="{{ route('days.update', $day) }}" method="POST" class="flex items-center gap-1">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $day->name }}" required class="bg-gray-800 border-gray-700 text-white text-xs rounded px-1.5 py-0.5">
                    <button type="submit" class="text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded">حفظ</button>
                    <button type="button" @click="editDay = false" class="text-[10px] bg-gray-700 text-gray-300 px-1.5 py-0.5 rounded">×</button>
                </form>
            </template>

            <form action="{{ route('days.destroy', $day) }}" method="POST" onsubmit="return confirm('حذف اليوم؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-400 hover:text-red-300">🗑️</button>
            </form>
        </div>

        <!-- قائمة تمارين اليوم -->
        <ul class="text-sm space-y-2 mb-4">
            @forelse($day->dayExercises as $item)
                <x-programs.exercise-row :item="$item" />
            @empty
                <li class="text-gray-500 text-xs italic py-2 text-center bg-gray-800/40 rounded-lg">لا يوجد تمارين مضافة</li>
            @endforelse
        </ul>
    </div>

    <!-- إضافة تمرين لليوم -->
    <x-programs.attach-exercise-form :day="$day" :grouped-exercises="$groupedExercises" />
</div>
