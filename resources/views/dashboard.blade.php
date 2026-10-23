<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
                <span>📊</span> الشاشة الرئيسية والأنشطة
            </h2>
            <a href="#today-workout-selector" data-open-workout-selector class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2">
                <span>➕</span> تسجيل تمرين اليوم / السجل
            </a>
        </div>
    </x-slot>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const startLink = document.querySelector('[data-open-workout-selector]');
            const workoutCard = document.getElementById('today-workout-selector');
            if (!startLink || !workoutCard) return;

            startLink.addEventListener('click', (event) => {
                event.preventDefault();
                const dayPicker = workoutCard.querySelector('details');
                if (dayPicker) dayPicker.open = true;
                workoutCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const firstDay = dayPicker?.querySelector('a[href]');
                if (firstDay) firstDay.focus({ preventScroll: true });
            });
        });
    </script>

    <!-- تضمين مكتبة Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="space-y-6">

        <!-- 1. كروت الملخص السريع (KPI Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- كارت تمرين اليوم: اختيار اليوم قبل الذهاب إلى شاشة التسجيل -->
            <x-workout.today-workout-card :active-program="$activeProgram" :today-workout="$todayWorkout" />

            <!-- كارت سعرات اليوم -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700/80 shadow-xl flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400">🔥 سعرات اليوم</p>
                        <h4 class="text-2xl font-black text-amber-400 mt-1">
                            {{ number_format($todayCalories) }} <span class="text-xs text-gray-400 font-normal">كالوري</span>
                        </h4>
                    </div>
                    <a href="{{ route('nutrition.index') }}" class="w-10 h-10 bg-amber-600/20 border border-amber-500/40 rounded-xl flex items-center justify-center text-xl shrink-0">
                        🥗
                    </a>
                </div>

                <div class="pt-2 border-t border-gray-700/60 flex items-center justify-between">
                    <span class="text-[11px] text-emerald-400 font-bold">🥩 {{ number_format($todayProtein, 1) }}ج بروتين</span>
                    <a href="{{ route('nutrition.index') }}" class="text-[11px] text-gray-400 hover:text-white font-bold transition">التفاصيل ←</a>
                </div>
            </div>

            <!-- كارت الوزن الحالي -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700/80 shadow-xl flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400">⚖️ أحدث وزن</p>
                        <h4 class="text-2xl font-black text-emerald-400 mt-1">
                            {{ $latestMeasurement->weight ?? '--' }} <span class="text-xs text-gray-400 font-normal">كجم</span>
                        </h4>
                    </div>
                    <a href="{{ route('measurements.index') }}" class="w-10 h-10 bg-emerald-600/20 border border-emerald-500/40 rounded-xl flex items-center justify-center text-xl shrink-0">
                        📏
                    </a>
                </div>

                <div class="pt-2 border-t border-gray-700/60 flex items-center justify-between">
                    <span class="text-[10px] text-gray-400">
                        {{ $latestMeasurement ? Carbon\Carbon::parse($latestMeasurement->date)->format('Y-m-d') : 'لا يوجد قياس' }}
                    </span>
                    <a href="{{ route('measurements.index') }}" class="text-[11px] text-emerald-400 hover:underline font-bold">+ إضافة وزن</a>
                </div>
            </div>

            <!-- كارت الكرياتين السريع -->
            <div x-data="{ taken: false }" class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700/80 shadow-xl flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400">💊 جرعة الكرياتين</p>
                        <h4 class="text-xs font-bold mt-1" :class="taken ? 'text-emerald-400' : 'text-amber-400'" x-text="taken ? 'تم تناول 5 جرام ✓' : 'لم تُسجل بعد'"></h4>
                    </div>
                    <div class="w-10 h-10 bg-purple-600/20 border border-purple-500/40 rounded-xl flex items-center justify-center text-xl shrink-0">
                        ⚡
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-700/60">
                    <button @click="taken = !taken" class="w-full text-[11px] font-bold py-1.5 rounded-lg transition shadow-md" :class="taken ? 'bg-gray-700 text-gray-300 hover:bg-gray-600' : 'bg-purple-600 text-white hover:bg-purple-500'">
                        <span x-text="taken ? 'إلغاء التحديد' : '✓ أخذت الكرياتين اليوم'"></span>
                    </button>
                </div>
            </div>

        </div>

        <!-- 2. الرسوم البيانية التفاعلية المحسنة -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- رسم بياني لتطور وزن الجسم -->
            <div class="bg-gray-800/90 p-6 rounded-2xl border border-gray-700/80 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-gray-700/80 pb-3">
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        📈 منحنى تغير وزن الجسم (كجم)
                    </h3>
                    <a href="{{ route('measurements.index') }}" class="text-[11px] text-emerald-400 hover:underline font-bold">عرض السجل كامل</a>
                </div>
                <div class="h-64 relative">
                    <canvas id="weightChart"></canvas>
                </div>
            </div>

            <!-- رسم بياني لاستهلاك السعرات في آخر 7 أيام -->
            <div class="bg-gray-800/90 p-6 rounded-2xl border border-gray-700/80 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-gray-700/80 pb-3">
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        🔥 استهلاك السعرات الحرارية (آخر 7 أيام)
                    </h3>
                    <a href="{{ route('nutrition.index') }}" class="text-[11px] text-amber-400 hover:underline font-bold">جدول التغذية</a>
                </div>
                <div class="h-64 relative">
                    <canvas id="nutritionChart"></canvas>
                </div>
            </div>

        </div>

    </div>

    <!-- Script لإعداد وتنسيق الرسوم البيانية -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 1. Weight Chart
            const weightCtx = document.getElementById('weightChart').getContext('2d');
            const weightGradient = weightCtx.createLinearGradient(0, 0, 0, 250);
            weightGradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
            weightGradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            new Chart(weightCtx, {
                type: 'line',
                data: {
                    labels: @json($weightChartLabels),
                    datasets: [{
                        label: 'الوزن (كجم)',
                        data: @json($weightChartData),
                        borderColor: '#10b981',
                        backgroundColor: weightGradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#064e3b',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo' } } },
                        y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo' } } }
                    }
                }
            });

            // 2. Nutrition Chart
            const nutritionCtx = document.getElementById('nutritionChart').getContext('2d');
            const barGradient = nutritionCtx.createLinearGradient(0, 0, 0, 250);
            barGradient.addColorStop(0, '#f59e0b');
            barGradient.addColorStop(1, '#d97706');

            new Chart(nutritionCtx, {
                type: 'bar',
                data: {
                    labels: @json($nutritionChartLabels),
                    datasets: [{
                        label: 'السعرات الحرارية',
                        data: @json($nutritionChartData),
                        backgroundColor: barGradient,
                        borderRadius: 10,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { family: 'Cairo' } } },
                        y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo' } } }
                    }
                }
            });

        });
    </script>
</x-app-layout>
