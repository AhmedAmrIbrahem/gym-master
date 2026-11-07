<style>
/* Timer-only layout safeguards for light and dark themes and small screens. */
[data-gym-timer] [hidden] { display: none !important; }
[data-gym-timer] .gym-timer-dial { width: min(224px, 64vw); height: min(224px, 64vw); min-width: min(224px, 64vw); flex-shrink: 0; position: relative; margin: 1.5rem auto; }
[data-gym-timer] .gym-timer-dial > svg { display: block; position: absolute; inset: 0; width: 100% !important; height: 100% !important; max-width: none; overflow: visible; }
[data-gym-timer] .gym-timer-dial-content { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .45rem; padding: 0 12%; text-align: center; }
[data-gym-timer] [data-timer-display] { width: 100%; white-space: nowrap; text-align: center; font-variant-numeric: tabular-nums; line-height: 1.15; font-size: 2.2rem; font-weight: 800; }
[data-gym-timer] [data-timer-sound] { appearance: auto; -webkit-appearance: checkbox; width: 20px; height: 20px; accent-color: #2563eb; color-scheme: light; flex: 0 0 auto; }
@media (min-width: 640px) { [data-gym-timer] .gym-timer-dial { width: 240px; height: 240px; min-width: 240px; } }
</style>
<div data-gym-timer data-user-id="{{ Auth::id() }}-{{ Auth::user()->created_at?->format('YmdHis.u') }}">
    <div data-timer-overlay hidden style="z-index: 100010;" class="fixed inset-0 flex items-end justify-center bg-black/75 p-0 backdrop-blur-sm sm:items-center sm:p-4" role="presentation">
        <section data-timer-panel tabindex="-1" role="dialog" dir="rtl" aria-modal="true" aria-labelledby="gym-timer-title"
            class="max-h-[calc(100dvh-12px)] w-full overflow-y-auto rounded-t-3xl border border-gray-700 bg-gray-800 p-4 pb-[max(1rem,env(safe-area-inset-bottom))] text-gray-100 shadow-2xl focus:outline-none sm:max-w-md sm:rounded-3xl sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3"><div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600/15 text-blue-400"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 1.5M9 2h6"/></svg></div>
                    <div><h2 id="gym-timer-title" class="text-base font-extrabold">الساعة والمؤقت</h2><p class="text-xs text-gray-400">أداة التوقيت أثناء التمرين وخارجه</p></div></div>
                <button data-timer-close type="button" aria-label="إغلاق النافذة" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-700 text-xl hover:bg-gray-600">×</button>
            </div>
            <div class="flex rounded-xl bg-gray-900/80 p-1" role="group" aria-label="نوع المؤقت">
                <button type="button" data-timer-mode="timer" class="min-h-11 flex-1 rounded-lg text-sm font-bold transition">المؤقت</button>
                <button type="button" data-timer-mode="stopwatch" class="min-h-11 flex-1 rounded-lg text-sm font-bold transition">ساعة الإيقاف</button>
            </div>
            <div class="gym-timer-dial" aria-live="off">
                <svg viewBox="0 0 112 112" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="transform:rotate(-90deg)">
                    <circle cx="56" cy="56" r="44" stroke="#374151" stroke-width="5" fill="none"/>
                    <circle data-timer-ring cx="56" cy="56" r="44" stroke="#3b82f6" stroke-width="5" stroke-linecap="round" fill="none" stroke-dasharray="276.46" stroke-dashoffset="0"/>
                </svg>
                <div class="gym-timer-dial-content">
                    <div data-timer-display dir="ltr" class="font-mono text-gray-100">01:30</div>
                    <p data-timer-hint class="text-xs font-bold text-gray-400">الوقت المتبقي</p>
                    <p data-timer-done hidden class="text-xs font-bold text-emerald-400" role="status">انتهى المؤقت ✓</p>
                </div>
            </div>
            <div data-timer-presets class="mb-3 grid grid-cols-4 gap-2">
                @foreach([30,60,90,120] as $duration)
                    <button type="button" data-timer-preset="{{ $duration }}" class="min-h-11 rounded-xl border border-gray-600 bg-gray-900/50 px-1 text-xs font-bold hover:border-blue-500 hover:text-blue-400">{{ $duration }} ثانية</button>
                @endforeach
            </div>
            <label data-timer-custom class="mb-4 flex items-center justify-between gap-2 text-xs text-gray-300">مدة مخصصة بالثواني
                <input data-timer-custom-input type="number" inputmode="numeric" min="1" max="35999" placeholder="مثال: 150" class="w-32 rounded-lg border-gray-600 bg-gray-900 text-sm text-white focus:border-blue-500 focus:ring-blue-500">
            </label>
            <div class="flex gap-2">
                <button type="button" data-timer-play class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-500"><span data-timer-play-icon>▶</span><span data-timer-play-label>بدء</span></button>
                <button type="button" data-timer-reset class="min-h-12 rounded-xl border border-gray-600 bg-gray-700 px-5 text-sm font-bold transition hover:bg-gray-600">إعادة ضبط</button>
            </div>
            <label class="mt-4 flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-gray-600/50 bg-gray-900/40 p-3 text-xs font-bold text-gray-300"><span>تنبيه صوتي عند انتهاء الوقت</span><input data-timer-sound type="checkbox" aria-label="تفعيل التنبيه الصوتي" class="h-5 w-5 shrink-0 cursor-pointer rounded border border-gray-400 bg-white text-blue-600 accent-blue-600 focus:ring-2 focus:ring-blue-500" style="accent-color:#2563eb;color-scheme:light"></label>
            <p class="mt-3 text-center text-[11px] text-gray-400">المؤقت يستمر أثناء التنقل بين صفحات النظام.</p>
        </section>
    </div>
</div>
