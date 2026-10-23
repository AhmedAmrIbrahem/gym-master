@props(['item'])

<li class="bg-gray-800/80 p-2 rounded-lg border border-gray-700 text-gray-200 flex justify-between items-center" x-data="{ editItem: false }">
    <span class="font-semibold text-white text-xs">• {{ $item->exercise->name }}</span>

    <template x-if="!editItem">
        <div class="flex items-center gap-1">
            <span class="text-[10px] text-gray-400 bg-gray-900 px-2 py-1 rounded border border-gray-700">
                {{ $item->default_sets }}×{{ $item->default_reps }}
            </span>
            <button @click="editItem = true" class="text-[10px] text-gray-400 hover:text-blue-400">✏️</button>
            <form action="{{ route('day-exercises.destroy', $item) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-[10px] text-red-400">×</button>
            </form>
        </div>
    </template>

    <template x-if="editItem">
        <form action="{{ route('day-exercises.update', $item) }}" method="POST" class="flex items-center gap-1">
            @csrf
            @method('PUT')
            <input type="number" name="default_sets" value="{{ $item->default_sets }}" class="w-10 bg-gray-900 border-gray-700 text-white text-[10px] rounded p-0.5 text-center">
            <span class="text-[10px]">×</span>
            <input type="number" name="default_reps" value="{{ $item->default_reps }}" class="w-10 bg-gray-900 border-gray-700 text-white text-[10px] rounded p-0.5 text-center">
            <button type="submit" class="text-[10px] bg-blue-600 text-white px-1.5 py-0.5 rounded">✓</button>
            <button type="button" @click="editItem = false" class="text-[10px] bg-gray-700 text-gray-300 px-1 py-0.5 rounded">×</button>
        </form>
    </template>
</li>
