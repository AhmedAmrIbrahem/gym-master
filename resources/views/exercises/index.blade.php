<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-white">💪 بنك التمارين المتاحة</h2>
    </x-slot>

    <div class="space-y-6" x-data="{ openEdit: false, editId: null, editName: '', editMuscle: '' }">
        <x-flash-success />

        <!-- Form إضافة تمرين جديد -->
        <x-ui.panel class="p-6">
            <h3 class="text-lg font-bold text-blue-400 mb-4">+ إضافة تمرين جديد لبنك التمارين</h3>
            <form action="{{ route('exercises.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">اسم التمرين</label>
                    <input type="text" name="name" placeholder="مثال: Incline Cable Flyes" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">العضلة المستهدفة</label>
                    <select name="target_muscle" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm focus:border-blue-500">
                        <option value="Chest">صدر (Chest)</option>
                        <option value="Back">ظهر (Back)</option>
                        <option value="Shoulders">أكتاف (Shoulders)</option>
                        <option value="Legs">رجلين (Legs)</option>
                        <option value="Arms">ذراعين (Arms)</option>
                        <option value="Abs">بطن (Abs)</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <x-ui.submit-button class="w-full py-2.5 px-4 transition shadow-lg">
                        حفظ التمرين
                    </x-ui.submit-button>
                </div>
            </form>
        </x-ui.panel>

        <!-- جدول التمارين -->
        <x-ui.panel class="overflow-hidden">
            <div class="p-4 border-b border-gray-700 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                <h4 class="font-bold text-gray-300">قائمة التمارين المسجلة ({{ $exercises->total() }})</h4>
                <form action="{{ route('exercises.index') }}" method="GET" role="search" class="exercise-search-form flex w-full sm:w-auto gap-2">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو العضلة..." aria-label="البحث في التمارين" class="min-w-0 flex-1 sm:w-64 bg-gray-900 border-gray-700 text-white text-sm rounded-xl">
                    <button type="submit" class="bg-blue-600 text-white rounded-xl px-4 py-2 text-sm font-bold">بحث</button>
                    @if(request()->filled('search'))
                        <a href="{{ route('exercises.index') }}" class="border border-gray-600 text-gray-300 rounded-xl px-3 py-2 text-sm inline-flex items-center">مسح</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto"><table class="w-full min-w-[620px] text-right text-sm text-gray-300">
                <thead class="bg-gray-900 text-gray-400 uppercase text-xs">
                <tr>
                    <th class="p-4">اسم التمرين</th>
                    <th class="p-4">العضلة المستهدفة</th>
                    <th class="p-4 text-center">الإجراءات</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                @forelse($exercises as $exercise)
                    <tr class="hover:bg-gray-700/50 transition">
                        <td class="p-4 font-bold text-white">{{ $exercise->name }}</td>
                        <td class="p-4">
                                <span class="bg-blue-900/50 text-blue-300 border border-blue-700 text-xs px-2.5 py-1 rounded-lg">
                                    {{ $exercise->target_muscle }}
                                </span>
                        </td>
                        <td class="p-4 text-center flex justify-center gap-2">
                            <button type="button"
                                    @click="openEdit = true; editId = {{ $exercise->id }}; editName = '{{ addslashes($exercise->name) }}'; editMuscle = '{{ $exercise->target_muscle }}'"
                                    class="text-blue-400 hover:text-blue-300 text-xs font-bold bg-blue-950/40 px-3 py-1.5 rounded-lg border border-blue-800">
                                ✏️ تعديل
                            </button>

                            <form action="{{ route('exercises.destroy', $exercise) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التمرين؟')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold bg-red-950/40 px-3 py-1.5 rounded-lg border border-red-800">
                                    🗑️ حذف
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-8 text-center text-gray-500">لا يوجد تمارين مضافة بعد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table></div>

            <div class="gym-pagination p-4 border-t border-gray-700" dir="rtl">
                {{ $exercises->withQueryString()->links() }}
            </div>
        </x-ui.panel>

        <!-- Modal التعديل -->
        <div x-show="openEdit" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50" x-cloak>
            <div class="bg-gray-800 border border-gray-700 p-6 rounded-2xl max-w-md w-full space-y-4">
                <h3 class="text-lg font-bold text-white">تعديل بيانات التمرين</h3>
                <form :action="'/exercises/' + editId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">اسم التمرين</label>
                        <input type="text" name="name" x-model="editName" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">العضلة المستهدفة</label>
                        <select name="target_muscle" x-model="editMuscle" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm">
                            <option value="Chest">Chest</option>
                            <option value="Back">Back</option>
                            <option value="Shoulders">Shoulders</option>
                            <option value="Legs">Legs</option>
                            <option value="Arms">Arms</option>
                            <option value="Abs">Abs</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openEdit = false" class="bg-gray-700 text-gray-300 px-4 py-2 rounded-xl text-xs font-bold">إلغاء</button>
                        <x-ui.submit-button class="px-4 py-2 text-xs">حفظ التعديلات</x-ui.submit-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
