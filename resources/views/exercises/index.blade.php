<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-white">💪 بنك التمارين المتاحة</h2>
    </x-slot>

    <style>
        /* Explicit breakpoints: works even without rebuilding Tailwind assets. */
        .gym-exercise-mobile { display: none; }
        .gym-exercise-desktop { display: block; }
        @media (max-width: 767px) {
            .gym-exercise-mobile { display: block; }
            .gym-exercise-desktop { display: none; }
            .gym-exercise-mobile article { padding: 14px 16px; border-bottom: 1px solid rgba(148,163,184,.16); }
            .gym-exercise-mobile article:last-child { border-bottom: 0; }
            .gym-exercise-mobile .gym-exercise-name { min-width: 0; font-size: 14px; font-weight: 700; line-height: 1.65; overflow-wrap: anywhere; }
            .gym-exercise-mobile .gym-exercise-tag { font-size: 11px; border: 1px solid #3b82f6; color: #93c5fd; background: rgba(37,99,235,.12); border-radius: 8px; padding: 4px 9px; flex-shrink: 0; }
            .gym-exercise-mobile .gym-exercise-action { display: inline-flex; align-items:center; justify-content:center; min-height:42px; padding: 0 14px; border-radius:10px; font-size:12px; font-weight:700; border: 1px solid; }
            .gym-exercise-mobile .gym-exercise-edit { color:#93c5fd; border-color:rgba(59,130,246,.3); background:rgba(37,99,235,.12); }
            .gym-exercise-mobile .gym-exercise-delete { color:#fca5a5; border-color:rgba(239,68,68,.3); background:rgba(220,38,38,.1); }
            html[data-theme="light"] .gym-exercise-mobile article { border-color:#e2e8f0; }
            html[data-theme="light"] .gym-exercise-mobile .gym-exercise-tag { color:#1d4ed8; background:#eff6ff; border-color:#bfd3ff; }
            html[data-theme="light"] .gym-exercise-mobile .gym-exercise-edit { color:#1d4ed8; background:#eff6ff; border-color:#c7d9ff; }
            html[data-theme="light"] .gym-exercise-mobile .gym-exercise-delete { color:#b91c1c; background:#fff1f2; border-color:#fecdd3; }
            .gym-exercise-mobile .gym-exercise-name { color:#f8fafc; }
            html[data-theme="light"] .gym-exercise-mobile .gym-exercise-name { color:#172033; }
        }

        /* Pagination on phones is rendered independently from Laravel's desktop links.
           This avoids the dark "Previous" disabled button under Light Mode. */
        .gym-exercise-pager-mobile { display: none; }
        .gym-exercise-pager-desktop { display: block; }
        @media (max-width: 767px) {
            .gym-exercise-pager-mobile { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
            .gym-exercise-pager-desktop { display: none; }
            .gym-exercise-pager-mobile .gym-page-link {
                display: inline-flex; justify-content: center; align-items: center;
                min-height: 44px; min-width: 94px; border-radius: 11px;
                padding: 9px 12px; border: 1px solid rgba(96,165,250,.4);
                background: rgba(37,99,235,.16); color: #bfdbfe;
                font-size: 13px; font-weight: 700; text-decoration: none;
            }
            .gym-exercise-pager-mobile .gym-page-link[aria-disabled="true"] {
                background: rgba(148,163,184,.08); border-color: rgba(148,163,184,.22);
                color: #94a3b8; opacity: .72; cursor: not-allowed;
            }
            .gym-exercise-pager-mobile .gym-page-status {
                color: #cbd5e1; font-size: 12px; font-weight: 600;
                white-space: nowrap; text-align: center;
            }
            html[data-theme="light"] .gym-exercise-pager-mobile .gym-page-link {
                background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8;
            }
            html[data-theme="light"] .gym-exercise-pager-mobile .gym-page-link[aria-disabled="true"] {
                background: #f1f5f9; border-color: #e2e8f0;
                color: #64748b; opacity: 1;
            }
            html[data-theme="light"] .gym-exercise-pager-mobile .gym-page-status { color: #475569; }
        }

        /* Scroll within the edit dialog on short iPhone/Android screens. */
        .gym-exercise-modal-overlay { overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
        .gym-exercise-modal-panel { max-height:min(calc(100dvh - 32px),calc(var(--gym-visual-viewport-height,100dvh) - 24px));
            overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
        @media (max-width:767px) {
            .gym-exercise-modal-overlay { align-items:flex-start !important; padding:12px 12px max(12px,env(safe-area-inset-bottom,0px)) !important; }
            .gym-exercise-modal-panel { padding:18px !important; border-radius:17px !important; }
            .gym-exercise-modal-panel input,.gym-exercise-modal-panel select { font-size:16px !important; min-height:44px; }
            .gym-exercise-modal-panel button { min-height:44px; }
            .exercise-search-form input,
            input[name="name"], select[name="target_muscle"] { font-size:16px !important; }
        }
    </style>

    <div class="space-y-6" x-data="{ openEdit: false, editId: null, editName: '', editMuscle: '' }">
        <x-flash-success />

        <!-- Form إضافة تمرين جديد -->
        <x-ui.panel class="p-4 sm:p-6 min-w-0">
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

            {{-- عرض الهاتف: كروت بدلاً من جدول بعرض ثابت --}}
            <div class="gym-exercise-mobile" dir="rtl">
                @forelse($exercises as $exercise)
                    <article class="space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <strong class="gym-exercise-name flex-1" dir="auto">{{ $exercise->name }}</strong>
                            <span class="gym-exercise-tag">{{ $exercise->target_muscle }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button"
                                    @click="openEdit = true; editId = {{ $exercise->id }}; editName = @js($exercise->name); editMuscle = @js($exercise->target_muscle)"
                                    class="gym-exercise-action gym-exercise-edit">✏️ تعديل</button>
                            <form action="{{ route('exercises.destroy', $exercise) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التمرين؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="gym-exercise-action gym-exercise-delete">🗑️ حذف</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center text-gray-400 text-sm">لا توجد تمارين مضافة بعد.</div>
                @endforelse
            </div>
            <div class="gym-exercise-desktop overflow-x-auto"><table class="w-full min-w-[620px] text-right text-sm text-gray-300">
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
                                    @click="openEdit = true; editId = {{ $exercise->id }}; editName = @js($exercise->name); editMuscle = @js($exercise->target_muscle)"
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

            @if($exercises->hasPages())
                <nav class="gym-pagination p-4 border-t border-gray-700" dir="rtl" aria-label="صفحات التمارين">
                    <div class="gym-exercise-pager-mobile">
                        @if($exercises->onFirstPage())
                            <span class="gym-page-link" aria-disabled="true">السابق</span>
                        @else
                            <a class="gym-page-link" href="{{ $exercises->previousPageUrl() }}" rel="prev">السابق</a>
                        @endif
                        <span class="gym-page-status">{{ $exercises->currentPage() }} / {{ $exercises->lastPage() }}</span>
                        @if($exercises->hasMorePages())
                            <a class="gym-page-link" href="{{ $exercises->nextPageUrl() }}" rel="next">التالي</a>
                        @else
                            <span class="gym-page-link" aria-disabled="true">التالي</span>
                        @endif
                    </div>
                    <div class="gym-exercise-pager-desktop">{{ $exercises->withQueryString()->links() }}</div>
                </nav>
            @endif
        </x-ui.panel>

        <!-- Modal التعديل -->
        <div x-show="openEdit" class="gym-exercise-modal-overlay fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50" x-cloak>
            <div class="gym-exercise-modal-panel bg-gray-800 border border-gray-700 p-6 rounded-2xl max-w-md w-full space-y-4">
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
