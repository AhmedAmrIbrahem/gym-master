<x-app-layout>
<style>
    /* Independent visibility rules: do not rely on generated Tailwind utilities. */
    .gym-measurements-mobile { display: none !important; }
    .gym-measurements-desktop { display: block !important; }
    @media (max-width: 767px) {
        .gym-measurements-mobile { display: block !important; }
        .gym-measurements-desktop { display: none !important; }
    }
</style>

    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-extrabold text-2xl text-white flex items-center gap-2">
                <span>⚖️</span> وزن الجسم والقياسات
            </h2>
            <span class="text-xs bg-purple-950 text-purple-300 border border-purple-800 px-3 py-1.5 rounded-xl font-bold">
                تتبع التطور البدني
            </span>
        </div>
    </x-slot>

    <div class="space-y-6">

        <x-flash-success class="font-bold text-sm" />

        <!-- 1. كروت الإحصائيات (KPI Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-600/20 border border-blue-500/40 rounded-xl flex items-center justify-center text-2xl">
                    ⚖️
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">آخر وزن مسجل</p>
                    <h4 class="text-2xl font-black text-white mt-0.5">
                        {{ $latest ? number_format($latest->weight, 2) : '--' }} <span class="text-xs text-gray-400">كجم</span>
                    </h4>
                </div>
            </div>

            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-600/20 border border-emerald-500/40 rounded-xl flex items-center justify-center text-2xl">
                    📈
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">التغير عن القياس السابق</p>
                    <h4 class="text-2xl font-black mt-0.5 {{ $weightChange > 0 ? 'text-emerald-400' : ($weightChange < 0 ? 'text-blue-400' : 'text-gray-300') }}">
                        @if($weightChange !== null)
                            {{ $weightChange > 0 ? '+' : '' }}{{ number_format($weightChange, 2) }} <span class="text-xs text-gray-400">كجم</span>
                        @else
                            --
                        @endif
                    </h4>
                </div>
            </div>

            <div class="bg-gray-800/90 border border-gray-700 p-5 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-600/20 border border-purple-500/40 rounded-xl flex items-center justify-center text-2xl">
                    📅
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">متوسط الوزن هذا الشهر</p>
                    <h4 class="text-2xl font-black text-purple-400 mt-0.5">
                        {{ $monthlyAvgWeight ? number_format($monthlyAvgWeight, 2) : '--' }} <span class="text-xs text-gray-400">كجم</span>
                    </h4>
                </div>
            </div>
        </div>

        <!-- 2. نموذج تسجيل قياس جديد -->
        <x-ui.panel class="p-4 sm:p-6 space-y-4 min-w-0">
            <h3 class="text-lg font-bold text-white border-b border-gray-700 pb-3 flex flex-wrap items-start justify-between gap-2">
                <span class="flex items-center gap-2">📝 إدخال قياسات اليوم</span>
                <span class="text-[11px] font-normal text-gray-400">* الوزن و الطول و التاريخ فقط إجباري، وباقي الحقول اختيارية</span>
            </h3>

            <form action="{{ route('measurements.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">التاريخ *</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm p-2.5 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">وزن الجسم (كجم) *</label>
                        <input type="number" step="0.1" name="weight" placeholder="مثال: 75.5" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm p-2.5 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1">الطول (سم) *</label>
                        <input type="number" step="0.1" name="height" placeholder="مثال: 174" required class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm p-2.5 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-1">محيط الذراع (سم) <span class="text-[10px] text-gray-500">(اختياري)</span></label>
                        <input type="number" step="0.1" name="biceps" placeholder="اختياري" class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm p-2.5 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-1">محيط الصدر (سم) <span class="text-[10px] text-gray-500">(اختياري)</span></label>
                        <input type="number" step="0.1" name="chest" placeholder="اختياري" class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm p-2.5 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-1">محيط الخصر (سم) <span class="text-[10px] text-gray-500">(اختياري)</span></label>
                        <input type="number" step="0.1" name="waist" placeholder="اختياري" class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm p-2.5 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-1">محيط الفخذ (سم) <span class="text-[10px] text-gray-500">(اختياري)</span></label>
                        <input type="number" step="0.1" name="thighs" placeholder="اختياري" class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm p-2.5 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1">ملاحظات <span class="text-[10px] text-gray-500">(اختياري)</span></label>
                    <input type="text" name="notes" placeholder="مثال: القياس صباحاً على الريق..." class="w-full bg-gray-900 border-gray-700 text-white rounded-xl text-sm p-2.5 focus:border-blue-500">
                </div>

                <div class="flex justify-end pt-2">
                    <x-ui.submit-button class="px-6 py-2.5 text-xs transition shadow-lg">
                        💾 حفظ القياس
                    </x-ui.submit-button>
                </div>
            </form>
        </x-ui.panel>

        <!-- 3. جدول السجل التاريخي للقياسات -->
        <x-ui.panel class="overflow-hidden">
            <div class="p-4 border-b border-gray-700 bg-gray-900/60 flex justify-between items-center">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>📜</span> سجل القياسات السابقة
                </h3>
            </div>

            {{-- عرض الهاتف: كروت كاملة بدل تمرير الجدول العريض --}}
            <div class="gym-measurements-mobile divide-y divide-gray-700/50" dir="rtl">
                @forelse($measurements as $item)
                    <article class="p-4 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($item->date)->locale('ar')->translatedFormat('l، d F Y') }}</span>
                            <span class="text-base font-black text-emerald-400" dir="rtl">{{ number_format($item->weight, 2) }} كجم</span>
                        </div>
                        <dl class="grid grid-cols-2 gap-2 text-xs">
                            @foreach(['الطول' => [$item->height, 'سم'], 'الذراع' => [$item->biceps, 'سم'], 'الصدر' => [$item->chest, 'سم'], 'الخصر' => [$item->waist, 'سم'], 'الفخذ' => [$item->thighs, 'سم']] as $label => $value)
                                <div class="min-w-0 rounded-xl border border-gray-700 bg-gray-900/50 p-3">
                                    <dt class="text-gray-400 mb-1">{{ $label }}</dt>
                                    <dd class="text-gray-100 font-bold break-words">{{ $value[0] !== null ? $value[0].' '.$value[1] : '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        @if($item->notes)
                            <p class="text-xs text-gray-300 break-words rounded-xl bg-gray-900/50 border border-gray-700 p-3"><span class="text-gray-400">ملاحظات: </span>{{ $item->notes }}</p>
                        @endif
                        <form action="{{ route('measurements.destroy', $item->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف القياس؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 px-4 rounded-xl border border-red-700/60 bg-red-950/30 text-red-300 font-bold text-xs">🗑️ حذف القياس</button>
                        </form>
                    </article>
                @empty
                    <p class="p-6 text-center text-gray-400 text-sm">لا توجد قياسات مسجلة بعد.</p>
                @endforelse
            </div>
            <div class="gym-measurements-desktop overflow-x-auto">
                <table class="w-full text-right text-xs text-gray-300">
                    <thead class="bg-gray-900 text-gray-400 uppercase">
                    <tr>
                        <th class="p-3">التاريخ</th>
                        <th class="p-3">الوزن</th>
                        <th class="p-3">الطول</th>
                        <th class="p-3">الذراع</th>
                        <th class="p-3">الصدر</th>
                        <th class="p-3">الخصر</th>
                        <th class="p-3">الفخذ</th>
                        <th class="p-3">ملاحظات</th>
                        <th class="p-3 text-center">إجراء</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                    @forelse($measurements as $item)
                        <tr class="hover:bg-gray-700/30 transition">
                            <td class="p-3 font-bold text-white">
                                {{ \Carbon\Carbon::parse($item->date)->locale('ar')->translatedFormat('l، d F Y') }}
                            </td>
                            <td class="p-3 font-black text-emerald-400 text-sm">
                                {{ number_format($item->weight, 2) }} كجم
                            </td>
                            <td class="p-3">{{ $item->height ? $item->height . ' سم' : '-' }}</td>
                            <td class="p-3">{{ $item->biceps ? $item->biceps . ' سم' : '-' }}</td>
                            <td class="p-3">{{ $item->chest ? $item->chest . ' سم' : '-' }}</td>
                            <td class="p-3">{{ $item->waist ? $item->waist . ' سم' : '-' }}</td>
                            <td class="p-3">{{ $item->thighs ? $item->thighs . ' سم' : '-' }}</td>
                            <td class="p-3 text-gray-400">{{ $item->notes ?? '-' }}</td>
                            <td class="p-3 text-center">
                                <form action="{{ route('measurements.destroy', $item->id) }}" method="POST" onsubmit="return confirm('هل أنت تأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-gray-400">
                                لا توجد قياسات مسجلة بعد. ابدأ بتسجيل قياس اليوم!
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-700">
                {{ $measurements->links() }}
            </div>
        </x-ui.panel>

    </div>
</x-app-layout>
