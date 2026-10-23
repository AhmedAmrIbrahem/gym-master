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
        @foreach($programs as $program)
            <x-programs.program-card :program="$program" :grouped-exercises="$groupedExercises" />
        @endforeach

    </div>
</x-app-layout>
