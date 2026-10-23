<x-app-layout>
    <x-slot name="header">
        <div dir="rtl" class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-extrabold text-white">🤖 إعدادات البوت</h2>
                <p class="mt-1 text-sm text-gray-300">إدارة أسئلة وإجابات الكوتش الذكي</p>
            </div>
        </div>
    </x-slot>

    <div dir="rtl" x-data="{ addOpen: {{ $errors->any() ? 'true' : 'false' }}, editId: null, viewId: null }" class="mx-auto max-w-7xl space-y-5 text-gray-100" @keydown.escape.window="addOpen = false; editId = null; viewId = null">
        @if(session('success'))
            <x-ui-alert type="success">✓ {{ session('success') }}</x-ui-alert>
        @endif
        @if($errors->any())
            <x-ui-alert type="error">
                <strong>راجع البيانات:</strong>
                <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui-alert>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-extrabold text-white">الأسئلة المسجلة</h3>
                <p class="text-sm text-gray-400">إجمالي الأسئلة: {{ $faqs->total() }}</p>
            </div>
            <button type="button" @click="addOpen = true" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-500">+ إضافة سؤال جديد</button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-700 bg-gray-800 shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[740px] text-right text-sm">
                    <thead class="border-b border-gray-600 bg-gray-900 text-gray-200">
                    <tr>
                        <th scope="col" class="px-5 py-4 font-bold">#</th>
                        <th scope="col" class="px-5 py-4 font-bold">السؤال</th>
                        <th scope="col" class="px-5 py-4 font-bold">الإجابة</th>
                        <th scope="col" class="px-5 py-4 font-bold">التصنيف</th>
                        <th scope="col" class="px-5 py-4 font-bold">الحالة</th>
                        <th scope="col" class="px-5 py-4 font-bold">الإجراءات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                    @forelse($faqs as $faq)
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-5 py-4 text-gray-400">{{ $faq->id }}</td>
                            <td class="max-w-[260px] break-words px-5 py-4 font-bold text-white">{{ $faq->question }}</td>
                            <td class="max-w-[350px] px-5 py-4 text-gray-300"><p class="line-clamp-2 whitespace-pre-line break-words">{{ $faq->answer }}</p></td>
                            <td class="px-5 py-4 text-gray-300">{{ $faq->category ?: 'عام' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-block whitespace-nowrap rounded-lg px-3 py-1 text-xs font-bold {{ $faq->is_active ? 'bg-emerald-900 text-emerald-200' : 'bg-amber-900 text-amber-200' }}">{{ $faq->is_active ? 'مفعّل' : 'معطّل' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2" aria-label="إجراءات السؤال">
                                    <!-- عرض -->
                                    <button type="button" @click="viewId = {{ $faq->id }}" title="عرض السؤال" aria-label="عرض السؤال"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-sky-500/40 bg-sky-500/10 text-sky-300 transition hover:bg-sky-500/25 focus-visible:outline focus-visible:outline-2 focus-visible:outline-sky-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <!-- تعديل -->
                                    <button type="button" @click="editId = {{ $faq->id }}" title="تعديل السؤال" aria-label="تعديل السؤال"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-500/40 bg-amber-500/10 text-amber-300 transition hover:bg-amber-500/25 focus-visible:outline focus-visible:outline-2 focus-visible:outline-amber-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z"/></svg>
                                    </button>
                                    <!-- حذف -->
                                    <form method="POST" action="{{ route('admin.chatbot.faqs.destroy', $faq) }}" onsubmit="return confirm('هل أنت متأكد من حذف السؤال نهائياً؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="حذف السؤال" aria-label="حذف السؤال"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-500/40 bg-red-500/10 text-red-300 transition hover:bg-red-500/25 focus-visible:outline focus-visible:outline-2 focus-visible:outline-red-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-300">لا توجد أسئلة حالياً. اضغط «إضافة سؤال جديد» للبدء.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-gray-200">{{ $faqs->links() }}</div>

        <!-- نافذة إضافة سؤال، في نفس الصفحة -->
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[100010] flex items-center justify-center bg-black/75 p-4" @click.self="addOpen = false">
            <section role="dialog" aria-modal="true" aria-label="إضافة سؤال جديد" class="max-h-[90dvh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-600 bg-gray-800 p-5 shadow-2xl sm:p-7">
                <div class="mb-5 flex items-center justify-between"><h3 class="text-lg font-bold text-white">إضافة سؤال جديد</h3><button type="button" @click="addOpen = false" class="rounded-lg p-2 text-gray-300 hover:bg-gray-700" aria-label="إغلاق">✕</button></div>
                <form method="POST" action="{{ route('admin.chatbot.faqs.store') }}" class="space-y-4">
                    @csrf
                    <div><label for="new-question" class="mb-1 block text-sm font-bold text-gray-100">السؤال *</label><input id="new-question" name="question" value="{{ old('question') }}" required maxlength="255" class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-white placeholder-gray-400" placeholder="اكتب السؤال"></div>
                    <div><label for="new-answer" class="mb-1 block text-sm font-bold text-gray-100">الإجابة *</label><textarea id="new-answer" name="answer" required maxlength="10000" rows="4" class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-white placeholder-gray-400" placeholder="اكتب الإجابة">{{ old('answer') }}</textarea></div>
                    <div><label for="new-category" class="mb-1 block text-sm font-bold text-gray-100">التصنيف (اختياري)</label><input id="new-category" name="category" value="{{ old('category') }}" maxlength="100" class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-white" placeholder="تغذية / تمارين / عام"></div>
                    <div><label for="new-aliases" class="mb-1 block text-sm font-bold text-gray-100">صياغات بديلة (كل صياغة في سطر)</label><textarea id="new-aliases" name="aliases" maxlength="3000" rows="3" class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-white" placeholder="اكتب كل صياغة في سطر">{{ old('aliases') }}</textarea></div>
                    <label class="flex items-center gap-2 text-sm text-white"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') == '1') class="rounded border-gray-500 bg-gray-900 text-blue-600"> مفعّل</label>
                    <div class="flex gap-3"><button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white hover:bg-blue-500">حفظ السؤال</button><button type="button" @click="addOpen = false" class="rounded-xl border border-gray-600 px-5 py-3 text-sm text-gray-200">إلغاء</button></div>
                </form>
            </section>
        </div>

        <!-- نافذة عرض التفاصيل (قراءة فقط) -->
        @foreach($faqs as $faq)
            <div x-show="viewId === {{ $faq->id }}" x-cloak class="fixed inset-0 z-[100010] flex items-center justify-center bg-black/75 p-4" @click.self="viewId = null">
                <section role="dialog" aria-modal="true" aria-label="تفاصيل السؤال" class="max-h-[90dvh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-600 bg-gray-800 p-5 text-gray-100 shadow-2xl sm:p-7">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <h3 class="text-lg font-bold text-white">تفاصيل السؤال #{{ $faq->id }}</h3>
                        <button type="button" @click="viewId = null" class="rounded-lg p-2 text-gray-300 hover:bg-gray-700" aria-label="إغلاق">✕</button>
                    </div>
                    <div class="space-y-4">
                        <div><h4 class="mb-1 text-sm font-bold text-blue-300">السؤال</h4><p class="break-words whitespace-pre-line rounded-xl border border-gray-700 bg-gray-900 p-3">{{ $faq->question }}</p></div>
                        <div><h4 class="mb-1 text-sm font-bold text-blue-300">الإجابة</h4><p class="break-words whitespace-pre-line rounded-xl border border-gray-700 bg-gray-900 p-3">{{ $faq->answer }}</p></div>
                        <div><h4 class="mb-1 text-sm font-bold text-blue-300">التصنيف</h4><p class="rounded-xl border border-gray-700 bg-gray-900 p-3">{{ $faq->category ?: 'عام' }}</p></div>
                        <div><h4 class="mb-1 text-sm font-bold text-blue-300">صياغات أخرى</h4><p class="break-words whitespace-pre-line rounded-xl border border-gray-700 bg-gray-900 p-3">{{ $faq->aliases ?: 'لا توجد صياغات أخرى' }}</p></div>
                        <div><h4 class="mb-1 text-sm font-bold text-blue-300">الحالة</h4><span class="inline-block rounded-lg px-3 py-1 text-sm font-bold {{ $faq->is_active ? 'bg-emerald-900 text-emerald-200' : 'bg-amber-900 text-amber-200' }}">{{ $faq->is_active ? 'مفعّل' : 'معطّل' }}</span></div>
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button type="button" @click="viewId = null; editId = {{ $faq->id }}" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-500">تعديل السؤال</button>
                        <button type="button" @click="viewId = null" class="rounded-xl border border-gray-600 px-5 py-2.5 text-sm text-gray-200 hover:bg-gray-700">إغلاق</button>
                    </div>
                </section>
            </div>
        @endforeach

        <!-- نوافذ التعديل: لكل سؤال، بنفس الصفحة -->
        @foreach($faqs as $faq)
            <div x-show="editId === {{ $faq->id }}" x-cloak class="fixed inset-0 z-[100010] flex items-center justify-center bg-black/75 p-4" @click.self="editId = null">
                <section role="dialog" aria-modal="true" aria-label="تعديل السؤال" class="max-h-[90dvh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-600 bg-gray-800 p-5 shadow-2xl sm:p-7">
                    <div class="mb-5 flex items-center justify-between"><h3 class="text-lg font-bold text-white">تعديل السؤال #{{ $faq->id }}</h3><button type="button" @click="editId = null" class="rounded-lg p-2 text-gray-300 hover:bg-gray-700" aria-label="إغلاق">✕</button></div>
                    <form method="POST" action="{{ route('admin.chatbot.faqs.update', $faq) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <div><label for="question-{{ $faq->id }}" class="mb-1 block text-sm font-bold text-gray-100">السؤال *</label><input id="question-{{ $faq->id }}" name="question" required maxlength="255" value="{{ $faq->question }}" class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-white"></div>
                        <div><label for="answer-{{ $faq->id }}" class="mb-1 block text-sm font-bold text-gray-100">الإجابة *</label><textarea id="answer-{{ $faq->id }}" name="answer" required maxlength="10000" rows="4" class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-white">{{ $faq->answer }}</textarea></div>
                        <div><label for="category-{{ $faq->id }}" class="mb-1 block text-sm font-bold text-gray-100">التصنيف</label><input id="category-{{ $faq->id }}" name="category" maxlength="100" value="{{ $faq->category }}" class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-white"></div>
                        <div><label for="aliases-{{ $faq->id }}" class="mb-1 block text-sm font-bold text-gray-100">الصياغات البديلة (كل صياغة في سطر)</label><textarea id="aliases-{{ $faq->id }}" name="aliases" maxlength="3000" rows="3" class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-white">{{ $faq->aliases }}</textarea></div>
                        <label class="flex items-center gap-2 text-sm text-white"><input type="checkbox" name="is_active" value="1" @checked($faq->is_active) class="rounded border-gray-500 bg-gray-900 text-blue-600"> مفعّل</label>
                        <div class="flex gap-3"><button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white hover:bg-blue-500">حفظ التعديل</button><button type="button" @click="editId = null" class="rounded-xl border border-gray-600 px-5 py-3 text-sm text-gray-200">إلغاء</button></div>
                    </form>
                </section>
            </div>
        @endforeach
    </div>
</x-app-layout>
