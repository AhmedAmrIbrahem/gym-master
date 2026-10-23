<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
            <span>📊</span> لوحة الإحصائيات العامة للمشتركين
        </h2>
    </x-slot>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="space-y-6">

        <!-- 1. كروت الملخص السريع -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400">👥 إجمالي المشتركين</p>
                    <h4 class="text-3xl font-black text-white mt-1">{{ number_format($totalUsers) }}</h4>
                    <p class="text-[10px] text-emerald-400 mt-1 font-bold">نشط: {{ $activeUsers }} | معطل: {{ $inactiveUsers }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-600/20 border border-blue-500/40 rounded-xl flex items-center justify-center text-2xl">
                    👥
                </div>
            </div>

            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400">✅ المشتركين النشطين</p>
                    <h4 class="text-3xl font-black text-emerald-400 mt-1">{{ number_format($activeUsers) }}</h4>
                    <p class="text-[10px] text-gray-400 mt-1">حسابات مفعلة ومتاحة</p>
                </div>
                <div class="w-12 h-12 bg-emerald-600/20 border border-emerald-500/40 rounded-xl flex items-center justify-center text-2xl">
                    ⚡
                </div>
            </div>

            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400">🏋️‍♂️ التمارين المسجلة</p>
                    <h4 class="text-3xl font-black text-purple-400 mt-1">{{ number_format($totalSessions) }}</h4>
                    <p class="text-[10px] text-purple-300 mt-1 font-bold">هذا الأسبوع: {{ $thisWeekSessions }} جلسة</p>
                </div>
                <div class="w-12 h-12 bg-purple-600/20 border border-purple-500/40 rounded-xl flex items-center justify-center text-2xl">
                    💪
                </div>
            </div>

            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700 shadow-xl flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400">🛑 الحسابات المعطلة</p>
                    <h4 class="text-3xl font-black text-red-400 mt-1">{{ number_format($inactiveUsers) }}</h4>
                    <p class="text-[10px] text-red-300 mt-1">بحاجة تفعيل أو مراجعة</p>
                </div>
                <div class="w-12 h-12 bg-red-600/20 border border-red-500/40 rounded-xl flex items-center justify-center text-2xl">
                    🚫
                </div>
            </div>

        </div>

        <!-- 2. رسم بياني وجدول أحدث التسجيلات -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- رسم بياني لنمو المشتركين -->
            <div class="lg:col-span-2 bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-gray-700 pb-3">
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        📈 انضمام المشتركين الجدد (آخر 6 أشهر)
                    </h3>
                </div>
                <div class="h-64 relative">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>

            <!-- أحدث المشتركين انضماماً -->
            <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-xl space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center border-b border-gray-700 pb-3 mb-3">
                        <h3 class="text-sm font-extrabold text-white">✨ أحدث المشتركين</h3>
                        <a href="{{ route('admin.users.index') }}" class="text-[11px] text-purple-400 hover:underline font-bold">إدارة الكل ←</a>
                    </div>

                    <div class="space-y-3">
                        @forelse($latestUsers as $u)
                            <div class="flex items-center justify-between p-2.5 bg-gray-900/60 rounded-xl border border-gray-700/60">
                                <div class="flex items-center gap-2.5 overflow-hidden">
                                    <img src="{{ $u->avatar_url }}" class="w-8 h-8 rounded-lg object-cover border border-gray-600 shrink-0">
                                    <div class="truncate">
                                        <p class="text-xs font-bold text-white truncate">{{ $u->name }}</p>
                                        <p class="text-[10px] text-gray-400 truncate">{{ $u->phone }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border {{ $u->is_active ? 'bg-emerald-900/40 text-emerald-300 border-emerald-500/40' : 'bg-red-900/40 text-red-300 border-red-500/40' }}">
                                    {{ $u->is_active ? 'مفعل' : 'معطل' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 italic text-center py-4">لا يوجد مشتركين مسجلين بعد.</p>
                        @endforelse
                    </div>
                </div>

                <a href="{{ route('admin.users.index') }}" class="w-full text-center bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold py-2.5 rounded-xl transition shadow-lg mt-4">
                    👥 الانتقال لصفحة إدارة المشتركين
                </a>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('growthChart').getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 250);
            gradient.addColorStop(0, 'rgba(147, 51, 234, 0.4)');
            gradient.addColorStop(1, 'rgba(147, 51, 234, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($userGrowthLabels),
                    datasets: [{
                        label: 'مشتركين جدد',
                        data: @json($userGrowthData),
                        borderColor: '#a855f7',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 6,
                        pointBackgroundColor: '#a855f7'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo' } } },
                        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo' }, stepSize: 1 } }
                    }
                }
            });
        });
    </script>
</x-app-layout>
