@props(['program', 'groupedExercises'])

<div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl space-y-6" x-data="{ editProgram: false }">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-700 pb-4">

        <!-- عنوان النظام وتعديله -->
        <div class="flex items-center gap-3">
            <template x-if="!editProgram">
                <div class="flex items-center gap-2">
                    <h3 class="text-2xl font-extrabold text-white">{{ $program->name }}</h3>
                    <button @click="editProgram = true" class="text-xs text-gray-400 hover:text-blue-400">✏️</button>
                </div>
            </template>

            <template x-if="editProgram">
                <form action="{{ route('programs.update', $program) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $program->name }}" required class="bg-gray-900 border-gray-700 text-white text-xs rounded-lg px-2 py-1">
                    <button type="submit" class="text-xs bg-blue-600 text-white px-2 py-1 rounded">حفظ</button>
                    <button type="button" @click="editProgram = false" class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">إلغاء</button>
                </form>
            </template>

            @if($program->is_active)
                <span class="text-xs bg-green-950 text-green-400 border border-green-700 px-3 py-1 rounded-full font-bold">
                    ✓ النظام المفعل حالياً
                </span>
            @endif
        </div>

        <!-- أزرار التفعيل والحذف -->
        <div class="flex items-center gap-2">
            @if(!$program->is_active)
                <form action="{{ route('programs.activate', $program) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-1.5 text-xs rounded-xl transition shadow">
                        تفعيل هذا النظام
                    </button>
                </form>
            @endif

            <form action="{{ route('programs.destroy', $program) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف النظام بالكامل؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-950/50 hover:bg-red-900 text-red-400 border border-red-800 px-3 py-1.5 text-xs font-bold rounded-xl transition">
                    🗑️ حذف النظام
                </button>
            </form>
        </div>
    </div>

    <!-- الأيام التابعة للنظام -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($program->days as $day)
            <x-programs.day-card :day="$day" :grouped-exercises="$groupedExercises" />
        @endforeach

        <!-- إضافة يوم جديد بالنظام -->
        <div class="bg-gray-900/50 p-4 rounded-xl border border-dashed border-gray-600 flex flex-col justify-center items-center min-h-[200px]">
            <form action="{{ route('programs.days.store', $program) }}" method="POST" class="w-full space-y-3">
                @csrf
                <label class="block text-xs font-bold text-gray-300 text-center">+ إضافة يوم جديد لهذا النظام</label>
                <input type="text" name="name" placeholder="اسم اليوم (مثال: Push)" required class="w-full bg-gray-800 border-gray-700 text-white placeholder-gray-500 text-xs rounded-lg focus:border-blue-500">
                <button type="submit" class="w-full bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold py-2 rounded-lg transition border border-gray-600">
                    إضافة اليوم
                </button>
            </form>
        </div>
    </div>
</div>
