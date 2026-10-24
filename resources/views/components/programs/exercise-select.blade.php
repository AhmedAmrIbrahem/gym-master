@props(['groupedExercises'])

<div class="gym-exercise-picker relative" x-data="{ search: '', open: false, selected: '', selectedName: '', choose(id, name) { this.selected = String(id); this.selectedName = name; this.search = ''; this.open = false; } }" @keydown.escape.window="open = false" @click.outside="open = false">
    <input type="hidden" name="exercise_id" :value="selected">
    <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.exerciseSearch.focus())" :aria-expanded="open.toString()" aria-haspopup="listbox" class="gym-exercise-trigger w-full bg-gray-800 border border-gray-700 text-white text-xs rounded-lg px-3 py-2.5 text-right flex items-center justify-between gap-3">
        <span x-text="selectedName || 'اختر تمرين من القائمة...'" class="min-w-0 truncate"></span>
        <span aria-hidden="true">▾</span>
    </button>
    <div x-show="open" x-cloak class="gym-exercise-popover absolute inset-x-0 z-40 mt-2 rounded-xl border border-gray-700 bg-gray-800 shadow-xl p-2" role="listbox" aria-label="قائمة التمارين">
        <input x-ref="exerciseSearch" type="search" x-model="search" placeholder="ابحث عن اسم تمرين أو عضلة..." aria-label="ابحث عن تمرين" class="w-full mb-2 bg-gray-900 border-gray-700 text-white rounded-lg text-sm">
        <div class="gym-exercise-results max-h-56 overflow-y-auto overscroll-contain space-y-1">
            @foreach($groupedExercises as $muscle => $exercises)
                <div class="gym-exercise-group" x-show="{{ Js::from(mb_strtolower($muscle)) }}.includes(search.toLocaleLowerCase()) || [...$el.querySelectorAll('[data-exercise-name]')].some(el => el.dataset.exerciseName.includes(search.toLocaleLowerCase()))">
                    <div class="text-xs text-blue-400 font-bold px-2 pt-2 pb-1">{{ $muscle }}</div>
                    @foreach($exercises as $exercise)
                        <button type="button" role="option" :aria-selected="selected === '{{ $exercise->id }}'" data-exercise-name="{{ mb_strtolower($exercise->name) }}" x-show="!search || {{ Js::from(mb_strtolower($exercise->name.' '.$muscle)) }}.includes(search.toLocaleLowerCase())" @click="choose({{ $exercise->id }}, {{ Js::from($exercise->name) }})" class="gym-exercise-option block w-full text-right rounded-lg text-sm px-3 py-2 text-gray-200 hover:bg-blue-600/20">{{ $exercise->name }}</button>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
