@props(['activeProgram' => null, 'todayWorkout' => null])

<div id="today-workout-selector" class="bg-gradient-to-br from-gray-800 to-gray-800/80 p-5 rounded-2xl border border-gray-700/80 shadow-xl flex flex-col justify-between space-y-3 relative scroll-mt-24">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-bold text-gray-400">🏋️‍♂️ تمرين اليوم</p>
            <h4 class="text-base font-extrabold text-white mt-1">
                {{ $todayWorkout ? 'تم إنجاز الجلسة ✓' : 'لم يُسجل بعد' }}
            </h4>
        </div>
        <div class="w-10 h-10 bg-blue-600/20 border border-blue-500/40 rounded-xl flex items-center justify-center text-xl shrink-0" aria-hidden="true">💪</div>
    </div>

    <div class="pt-2 border-t border-gray-700/60 space-y-2">
        <span class="text-[10px] text-blue-400 font-bold block truncate" title="{{ $activeProgram->name ?? 'لا يوجد نظام نشط' }}">
            {{ $activeProgram->name ?? 'لا يوجد نظام نشط' }}
        </span>

        @if($activeProgram && $activeProgram->days->isNotEmpty())
            <button type="button" data-gym-day-toggle aria-haspopup="dialog" aria-expanded="false" aria-controls="gym-day-picker" class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2.5 text-[11px] font-bold text-white shadow-md transition hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                <span>⚡ اختر يوم التمرين</span>
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            {{-- Native dialog uses the browser top layer and never expands or clips the dashboard cards. --}}
            <dialog id="gym-day-picker" data-gym-day-dialog dir="rtl" aria-labelledby="gym-day-picker-title" aria-describedby="gym-day-picker-desc" class="gym-day-dialog">
                <div class="gym-day-dialog-shell">
                    <header class="gym-day-dialog-header">
                        <div class="min-w-0">
                            <p class="gym-day-dialog-eyebrow">البرنامج التدريبي</p>
                            <h2 id="gym-day-picker-title" class="gym-day-dialog-title">اختر يوم التمرين</h2>
                            <p id="gym-day-picker-desc" class="gym-day-dialog-subtitle">{{ $activeProgram->name }} · {{ $activeProgram->days->count() }} {{ $activeProgram->days->count() === 1 ? 'يوم' : 'أيام' }}</p>
                        </div>
                        <button type="button" data-gym-day-close class="gym-day-dialog-close" aria-label="إغلاق نافذة اختيار اليوم">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="19" height="19" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </header>
                    <div class="gym-day-dialog-search-wrap">
                        <label class="gym-day-dialog-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="19" height="19" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                            <input data-gym-day-search type="search" placeholder="ابحث عن يوم التمرين..." aria-label="بحث في أيام التمرين" autocomplete="off" spellcheck="false">
                            <kbd class="gym-day-dialog-search-hint" aria-hidden="true">بحث</kbd>
                        </label>
                    </div>
                    <div class="gym-day-dialog-results">
                        <p class="gym-day-dialog-count" aria-live="polite" data-gym-day-results-count>كل أيام التمرين ({{ $activeProgram->days->count() }})</p>
                        <nav class="gym-day-dialog-grid" aria-label="أيام البرنامج التدريبي">
                            @foreach($activeProgram->days as $day)
                                <a href="{{ route('workout.start', $day) }}" data-gym-day-item data-search-name="{{ $day->name }}" class="gym-day-dialog-option">
                                    <span class="gym-day-dialog-option-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="20" height="20" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6v12M18 6v12M3 8v8M21 8v8M6 12h12"/></svg>
                                    </span>
                                    <span class="gym-day-dialog-option-text" title="{{ $day->name }}">{{ $day->name }}</span>
                                    <svg class="gym-day-dialog-option-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 7-5 5 5 5"/></svg>
                                </a>
                            @endforeach
                        </nav>
                        <div data-gym-day-empty hidden class="gym-day-dialog-empty">مفيش أيام مطابقة للبحث. جرّب اسم تاني.</div>
                    </div>
                    <footer class="gym-day-dialog-footer">
                        <span>اختار اليوم لفتح شاشة تسجيل التمرين</span>
                        <button type="button" data-gym-day-close class="gym-day-dialog-cancel">إلغاء</button>
                    </footer>
                </div>
            </dialog>
        @elseif($activeProgram)
            <a href="{{ route('programs.index') }}" class="block rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-center text-[11px] font-bold px-3 py-2 transition">أضف أيامًا للنظام أولًا</a>
        @else
            <a href="{{ route('programs.index') }}" class="block rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-center text-[11px] font-bold px-3 py-2 transition">أنشئ أو فعّل نظامًا تدريبيًا</a>
        @endif
    </div>
</div>

<style>
    .gym-day-dialog {position:fixed;inset:0;margin:auto;box-sizing:border-box;width:min(720px,calc(100vw - 28px));max-width:none;max-height:min(680px,calc(100dvh - 36px));padding:0;overflow:hidden;border:1px solid #344860;border-radius:22px;background:#1b2739;color:#f1f5f9;box-shadow:0 32px 85px rgba(0,0,0,.48);}
    .gym-day-dialog:not([open]){display:none}
    .gym-day-dialog::backdrop{background:rgba(5,12,25,.76);backdrop-filter:blur(6px)}
    .gym-day-dialog-shell{display:flex;flex-direction:column;min-height:0;max-height:min(680px,calc(100dvh - 36px));}
    .gym-day-dialog-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:24px 24px 18px;border-bottom:1px solid #34445b;}
    .gym-day-dialog-eyebrow{font-size:11px;color:#60a5fa;font-weight:800;margin:0 0 5px;}
    .gym-day-dialog-title{font-size:21px;line-height:1.5;font-weight:900;margin:0;}
    .gym-day-dialog-subtitle{font-size:12px;color:#a0afc6;margin:5px 0 0;overflow-wrap:anywhere;}
    .gym-day-dialog-close{display:grid;place-items:center;flex-shrink:0;width:36px;height:36px;border:1px solid #42536b;border-radius:11px;background:#26364c;color:#bfd0e6;cursor:pointer;}
    .gym-day-dialog-close:hover{background:#364b65;color:white;}
    .gym-day-dialog-search-wrap{padding:17px 24px 7px;}
    .gym-day-dialog-search{display:flex;align-items:center;gap:11px;min-height:47px;padding:0 14px;border:1px solid #40536d;border-radius:13px;background:#111c2e;color:#8da8cb;transition:border-color .15s;}
    .gym-day-dialog-search:focus-within{border-color:#3b82f6;box-shadow:0 0 0 2px rgba(59,130,246,.12)}
    .gym-day-dialog-search input{flex:1;min-width:0;border:0;outline:0;box-shadow:none;background:transparent;color:#f1f5f9;font:inherit;font-size:13px;padding:11px 0;}
    .gym-day-dialog-search input::placeholder{color:#879bb7;}
    .gym-day-dialog-search input::-webkit-search-cancel-button{cursor:pointer}
    .gym-day-dialog-search-hint{font-size:10px;color:#8093ab;}
    .gym-day-dialog-results{overflow-y:auto;min-height:0;padding:8px 24px 22px;overscroll-behavior:contain;scrollbar-width:thin;scrollbar-color:#405877 transparent;}
    .gym-day-dialog-results::-webkit-scrollbar{width:5px;}
    .gym-day-dialog-results::-webkit-scrollbar-thumb{background:#405877;border-radius:99px;}
    .gym-day-dialog-count{font-size:11px;color:#a2b2cb;margin:7px 0 14px;font-weight:700;}
    .gym-day-dialog-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;}
    .gym-day-dialog-option{display:flex;align-items:center;gap:11px;min-width:0;min-height:64px;padding:12px;border:1px solid #354963;border-radius:14px;background:#222f43;color:#eef4fd;text-decoration:none;transition:border-color .16s,background-color .16s,transform .16s;}
    .gym-day-dialog-option:hover,.gym-day-dialog-option:focus-visible{outline:none;border-color:#508be6;background:#293e5c;transform:translateY(-1px);}
    .gym-day-dialog-option[hidden]{display:none}
    .gym-day-dialog-option-icon{display:grid;place-items:center;flex-shrink:0;width:38px;height:38px;border-radius:11px;background:rgba(59,130,246,.12);color:#60a5fa;}
    .gym-day-dialog-option-text{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:800;}
    .gym-day-dialog-option-arrow{flex-shrink:0;color:#7b9bd1;}
    .gym-day-dialog-empty{padding:36px 12px;text-align:center;color:#a2b2cb;font-size:13px;}
    .gym-day-dialog-empty[hidden]{display:none}
    .gym-day-dialog-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 24px;border-top:1px solid #34445b;color:#8da3c0;font-size:11px;}
    .gym-day-dialog-cancel{border:1px solid #415571;border-radius:10px;background:#26364c;color:#d5e3f3;padding:9px 18px;cursor:pointer;font-size:12px;font-weight:800;}
    .gym-day-dialog-cancel:hover{background:#344863;}
    html[data-theme="light"] .gym-day-dialog{background:#fff;border-color:#dbe5f1;color:#1d2d45;}
    html[data-theme="light"] .gym-day-dialog-header,html[data-theme="light"] .gym-day-dialog-footer{border-color:#e3eaf3;}
    html[data-theme="light"] .gym-day-dialog-subtitle,html[data-theme="light"] .gym-day-dialog-count,html[data-theme="light"] .gym-day-dialog-footer{color:#6b7d96;}
    html[data-theme="light"] .gym-day-dialog-search{background:#f6f9fd;border-color:#d9e4ef;}
    html[data-theme="light"] .gym-day-dialog-search input{color:#1d2d45;}
    html[data-theme="light"] .gym-day-dialog-option{background:#f6f9fd;border-color:#e0e8f2;color:#1d2d45;}
    html[data-theme="light"] .gym-day-dialog-option:hover{background:#eaf2ff;border-color:#94b6ee;}
    html[data-theme="light"] .gym-day-dialog-close,html[data-theme="light"] .gym-day-dialog-cancel{background:#eef3fa;border-color:#d8e4f0;color:#33445c;}
    @media(max-width:600px){.gym-day-dialog{border-radius:18px;max-height:calc(100dvh - 24px)}.gym-day-dialog-shell{max-height:calc(100dvh - 24px)}.gym-day-dialog-header{padding:18px 16px 15px}.gym-day-dialog-title{font-size:18px}.gym-day-dialog-search-wrap{padding:14px 16px 6px}.gym-day-dialog-results{padding:8px 16px 16px}.gym-day-dialog-grid{grid-template-columns:minmax(0,1fr);gap:8px}.gym-day-dialog-option{min-height:55px}.gym-day-dialog-footer{padding:12px 16px}}
    @media(prefers-reduced-motion:reduce){.gym-day-dialog-option{transition:none}.gym-day-dialog-option:hover{transform:none}}
</style>

<script>
    (() => {
        const setupGymDayDialog = () => {
            const card = document.getElementById('today-workout-selector');
            const trigger = card?.querySelector('[data-gym-day-toggle]');
            const dialog = card?.querySelector('[data-gym-day-dialog]');
            if (!trigger || !dialog || trigger.dataset.gymDayReady) return;
            trigger.dataset.gymDayReady = 'true';
            const search = dialog.querySelector('[data-gym-day-search]');
            const items = Array.from(dialog.querySelectorAll('[data-gym-day-item]'));
            const count = dialog.querySelector('[data-gym-day-results-count]');
            const empty = dialog.querySelector('[data-gym-day-empty]');
            const total = items.length;
            const filter = () => {
                const term = (search.value || '').trim().toLocaleLowerCase();
                let visible = 0;
                items.forEach(item => {
                    const match = (item.dataset.searchName || '').toLocaleLowerCase().includes(term);
                    item.hidden = !match;
                    if (match) visible++;
                });
                count.textContent = term ? `نتائج البحث (${visible})` : `كل أيام التمرين (${total})`;
                empty.hidden = visible !== 0;
            };
            const open = () => {
                if (dialog.open) return;
                search.value = '';
                filter();
                dialog.showModal();
                trigger.setAttribute('aria-expanded', 'true');
                search.focus({ preventScroll: true });
            };
            trigger.addEventListener('click', open);
            search.addEventListener('input', filter);
            dialog.querySelectorAll('[data-gym-day-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
            dialog.addEventListener('close', () => trigger.setAttribute('aria-expanded', 'false'));
            dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setupGymDayDialog);
        else setupGymDayDialog();
    })();
</script>
