@props(['groupedExercises'])

<select name="exercise_id" required class="w-full bg-gray-800 border-gray-700 text-white text-xs rounded-lg focus:border-blue-500">
    <option value="" class="text-gray-500">اختر تمرين...</option>
    @foreach($groupedExercises as $muscle => $exercises)
        <optgroup label="💪 {{ $muscle }}" class="bg-gray-900 text-blue-400 font-bold">
            @foreach($exercises as $exercise)
                <option value="{{ $exercise->id }}" class="bg-gray-900 text-white font-normal">{{ $exercise->name }}</option>
            @endforeach
        </optgroup>
    @endforeach
</select>
