<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-white">
            📅 إدارة الأنظمة التدريبية
        </h2>
    </x-slot>

    <div class="space-y-6">

        <x-flash-success />

        <!-- نموذج إنشاء نظام جديد -->
        <x-ui.panel class="p-6">
            <h3 class="text-lg font-bold text-blue-400 mb-4">+ إضافة نظام جديد (مثال: PPL أو Upper/Lower)</h3>
            <form action="{{ route('programs.store') }}" method="POST" class="flex flex-col sm:flex-row gap-4">
                @csrf
                <input type="text" name="name" placeholder="اسم النظام (مثلاً: PPL)..." required class="flex-1 bg-gray-900 border-gray-700 text-white placeholder-gray-500 rounded-xl text-sm focus:border-blue-500">
                <x-ui.submit-button class="px-6 py-2.5 transition shadow-lg">حفظ النظام</x-ui.submit-button>
            </form>
        </x-ui.panel>

        <!-- عرض الأنظمة الحالية -->
        @forelse($programs as $program)
            <x-programs.program-card :program="$program" />
        @empty
            <x-ui.panel class="p-6 text-center text-gray-300">
                مفيش أنظمة تدريبية لسه. أضف أول نظام من النموذج اللي فوق.
            </x-ui.panel>
        @endforelse

        @if($programs->hasPages())
            <nav aria-label="صفحات الأنظمة التدريبية" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-700 bg-gray-800 p-3 sm:p-4">
                <span class="text-xs font-bold text-gray-300">صفحة {{ $programs->currentPage() }} من {{ $programs->lastPage() }} — {{ $programs->total() }} نظام</span>
                <div class="flex flex-wrap gap-2">
                    @if($programs->onFirstPage())
                        <span aria-disabled="true" class="rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-xs text-gray-400 opacity-60">السابق</span>
                    @else
                        <a rel="prev" href="{{ $programs->previousPageUrl() }}" class="rounded-lg border border-gray-600 bg-gray-700 px-4 py-2 text-xs font-bold text-white hover:bg-gray-600">السابق</a>
                    @endif
                    @if($programs->hasMorePages())
                        <a rel="next" href="{{ $programs->nextPageUrl() }}" class="rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500">التالي</a>
                    @else
                        <span aria-disabled="true" class="rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-xs text-gray-400 opacity-60">التالي</span>
                    @endif
                </div>
            </nav>
        @endif

    </div>
    {{-- Exercise picker layout and theme styles live in resources/css/app.css. --}}
</x-app-layout>
