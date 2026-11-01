@props(['day'])

<form action="{{ route('days.exercises.attach', $day) }}" method="POST" class="space-y-2 border-t border-gray-800 pt-3">
    @csrf
    <x-programs.exercise-select />
    <div class="flex gap-2">
        <div class="w-1/2">
            <input type="number" name="default_sets" value="3" placeholder="مجموعات" class="w-full bg-gray-800 border-gray-700 text-white text-xs rounded-lg">
        </div>
        <div class="w-1/2">
            <input type="number" name="default_reps" value="10" placeholder="عدات" class="w-full bg-gray-800 border-gray-700 text-white text-xs rounded-lg">
        </div>
    </div>
    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-2 rounded-lg transition">
        + إضافة التمرين
    </button>
</form>
