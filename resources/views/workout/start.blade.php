<x-app-layout>
    <style>
        /* Consistent two-field set entry on narrow phones, even before CSS rebuild. */
        .gym-workout-set-row { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem; align-items:center; min-width:0; }
        .gym-workout-set-label { grid-column:1/-1; }
        .gym-workout-set-row input { display:block; width:100%; min-width:0; box-sizing:border-box; }
        /* Compact rest-time settings popover; independent of utility CSS build. */
        .gym-rest-config { position:relative; display:inline-flex; align-items:center; }
        .gym-rest-trigger { display:inline-flex; width:2rem; height:2rem; align-items:center; justify-content:center; border-radius:.65rem; border:1px solid #42516a; background:#263448; color:#b8d3ff; transition:background .15s, border-color .15s; }
        .gym-rest-trigger:hover,.gym-rest-trigger[aria-expanded="true"] { background:#224477; border-color:#5088ed; color:#fff; }
        .gym-rest-trigger:focus-visible { outline:2px solid #60a5fa; outline-offset:2px; }
        .gym-rest-popover { position:absolute; z-index:60; inset-inline-start:0; top:calc(100% + .65rem); width:min(18.5rem,calc(100vw - 2.5rem)); padding:1rem; border-radius:1rem; background:#182232; color:#f8fafc; border:1px solid #3a4d69; box-shadow:0 16px 38px rgba(0,0,0,.24); text-align:right; }
        .gym-rest-popover[hidden] { display:none!important; }
        .gym-rest-stepper { display:grid; grid-template-columns:2.75rem minmax(0,1fr) 2.75rem; align-items:stretch; height:2.85rem; direction:ltr; border:1px solid #455771; border-radius:.75rem; background:#0f1725; overflow:hidden; }
        .gym-rest-stepper button { display:grid; place-items:center; font-size:1.4rem; line-height:1; font-weight:600; color:#93bbff; border:0; background:transparent; cursor:pointer; }
        .gym-rest-stepper button:hover { background:#253955; }
        .gym-rest-stepper button:focus-visible { outline:2px solid #60a5fa; outline-offset:-2px; }
        .gym-rest-stepper input { display:block; min-width:0; width:100%; height:100%; text-align:center; border:0; border-inline:1px solid #455771; border-radius:0; background:transparent; color:#fff; font-size:1rem; font-weight:800; font-variant-numeric:tabular-nums; padding:0 .2rem; box-shadow:none; }
        .gym-rest-stepper input:focus { border-color:#60a5fa; box-shadow:none; outline:0; }
        .gym-rest-stepper input::-webkit-outer-spin-button,.gym-rest-stepper input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        .gym-rest-stepper input { appearance:textfield; -moz-appearance:textfield; }
        .gym-rest-compact { white-space:nowrap; min-height:2rem; }
        html[data-theme="light"] .gym-rest-compact { color:#1d4ed8!important; background:#eff6ff!important; border-color:#bfdbfe!important; }
        html[data-theme="light"] .gym-rest-compact:hover { background:#dbeafe!important; }
        html[data-theme="light"] .gym-rest-trigger { background:#eff5ff!important; border-color:#cfddf2!important; color:#2563eb!important; }
        html[data-theme="light"] .gym-rest-trigger:hover,html[data-theme="light"] .gym-rest-trigger[aria-expanded="true"] { background:#dfeaff!important; border-color:#8ab3f8!important; }
        html[data-theme="light"] .gym-rest-popover { background:#fff!important; color:#172b4a!important; border-color:#d5e1ef!important; box-shadow:0 16px 40px rgba(27,53,87,.15)!important; }
        html[data-theme="light"] .gym-rest-stepper { background:#f8faff!important; border-color:#ccdaee!important; }
        html[data-theme="light"] .gym-rest-stepper button { color:#2563eb!important; }
        html[data-theme="light"] .gym-rest-stepper button:hover { background:#eaf1fc!important; }
        html[data-theme="light"] .gym-rest-stepper input { color:#172b4a!important; border-color:#ccdaee!important; background:transparent!important; }
        @media(min-width:640px) {
            .gym-workout-set-row { grid-template-columns:6.5rem repeat(2,minmax(0,1fr)); gap:.75rem; }
            .gym-workout-set-label { grid-column:auto; }
        }
    </style>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-white min-w-0 break-words">
            🏋️‍♂️ جلسة تمرين: {{ $day->name }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        <x-flash-success class="font-bold" />

        @if($day->dayExercises->isEmpty())
            <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 sm:p-10 text-center shadow-xl max-w-xl mx-auto">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-blue-600/10 border border-blue-500/30 flex items-center justify-center text-2xl" aria-hidden="true">🏋️</div>
                <h3 class="text-lg font-extrabold text-white mb-2">لا توجد تمارين مضافة لهذا اليوم</h3>
                <p class="text-sm text-gray-400 leading-7 mb-6">يوم {{ $day->name }} لسه مفيهوش تمارين. أضف التمارين من إدارة الأنظمة وبعدها ارجع لتسجيل جلستك.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('programs.index') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl px-5 py-3 transition">إدارة الأنظمة وإضافة التمارين</a>
                </div>
            </div>
        @else
        @foreach($day->dayExercises as $dayExercise)
            @php
                $ex = $dayExercise->exercise;
                $last = $previousLogs[$ex->id] ?? null;
                $todayLogs = $savedTodayLogs[$ex->id] ?? collect();
                $isSavedToday = $todayLogs->count() > 0;
            @endphp

            <div data-timer-exercise="{{ $dayExercise->id }}" class="bg-gray-800 p-4 sm:p-6 rounded-2xl border {{ $isSavedToday ? 'border-green-600/60' : 'border-gray-700' }} shadow-xl space-y-4">
                <form action="{{ route('workout.store-single') }}" method="POST">
                    @csrf
                    <input type="hidden" name="workout_day_id" value="{{ $day->id }}">
                    <input type="hidden" name="exercise_id" value="{{ $ex->id }}">

                    <!-- هيدر التمرين: إعدادات الراحة ومؤقت واحد لكل تمرين -->
                    <div class="flex flex-wrap justify-between items-center gap-3 border-b border-gray-700 pb-3 mb-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 min-w-0">
                                <h3 class="text-lg sm:text-xl font-bold text-white min-w-0 break-words">{{ $ex->name }}</h3>
                                @if($isSavedToday)
                                    <span class="bg-green-950 text-green-400 border border-green-700 text-[10px] px-2 py-0.5 rounded-full font-bold">✓ تم الحفظ اليوم</span>
                                @endif
                            </div>
                            @if($last)
                                <p class="mt-1 text-[11px] text-gray-400">آخر أداء: {{ $last->weight }} كجم × {{ $last->reps }} عدات</p>
                            @endif
                            <div class="mt-2 flex flex-wrap items-center gap-2" dir="rtl">
                                <span class="text-xs bg-blue-950 text-blue-300 border border-blue-800 px-2.5 py-1 rounded-md inline-flex items-center">{{ $ex->target_muscle }}</span>
                                <div class="gym-rest-config" data-rest-config>
                                    <button type="button" class="gym-rest-trigger" data-rest-trigger aria-label="ضبط مدة الراحة لتمرين {{ $ex->name }}" aria-expanded="false" aria-controls="gym-rest-panel-{{ $dayExercise->id }}" title="إعدادات مدة الراحة">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.07.07-1.8 1.8-.07-.07A1.7 1.7 0 0 0 16 18.36a1.7 1.7 0 0 0-.99 1.55V20h-2.55v-.09a1.7 1.7 0 0 0-1.01-1.55 1.7 1.7 0 0 0-1.87.34l-.07.07-1.8-1.8.07-.07A1.7 1.7 0 0 0 8.13 15a1.7 1.7 0 0 0-1.55-.99H6.5v-2.55h.08a1.7 1.7 0 0 0 1.55-1.01 1.7 1.7 0 0 0-.34-1.87l-.07-.07 1.8-1.8.07.07A1.7 1.7 0 0 0 11.45 7a1.7 1.7 0 0 0 1.01-1.55V5.4h2.55v.08A1.7 1.7 0 0 0 16 7a1.7 1.7 0 0 0 1.87-.34l.07-.07 1.8 1.8-.07.07a1.7 1.7 0 0 0-.34 1.87 1.7 1.7 0 0 0 1.55 1.01h.08v2.55h-.08A1.7 1.7 0 0 0 19.4 15Z" transform="translate(-.3 -.3) scale(.96)"/></svg>
                                    </button>
                                    <div id="gym-rest-panel-{{ $dayExercise->id }}" class="gym-rest-popover" data-rest-popover role="group" aria-label="إعداد مدة الراحة" dir="rtl" hidden>
                                        <div class="flex items-center gap-2 text-sm font-bold mb-3">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-400 shrink-0" aria-hidden="true"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 1.5M9 2h6"/></svg>
                                            <span>مدة الراحة بين المجموعات</span>
                                        </div>
                                        <div class="flex items-center gap-3" dir="rtl">
                                            <div class="gym-rest-stepper flex-1">
                                                <button type="button" data-rest-minus aria-label="تقليل مدة الراحة 5 ثوانٍ">−</button>
                                                <input id="gym-rest-{{ $dayExercise->id }}" data-timer-exercise-duration type="number" inputmode="numeric" min="1" max="35999" value="90" dir="ltr" aria-label="مدة الراحة لتمرين {{ $ex->name }} بالثواني">
                                                <button type="button" data-rest-plus aria-label="زيادة مدة الراحة 5 ثوانٍ">+</button>
                                            </div>
                                            <span class="text-xs text-gray-400 shrink-0" data-rest-unit>ثانية</span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-3 leading-5" data-rest-hint>تُطبق على مجموعات هذا التمرين فقط</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" data-timer-rest="90"
                            class="gym-rest-compact inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg border border-blue-500/30 bg-blue-600/10 px-2.5 py-1.5 text-[11px] sm:text-xs font-bold text-blue-300 transition hover:bg-blue-600/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-400"
                            aria-label="بدء راحة 90 ثانية" title="بدء مؤقت الراحة">
                            <svg width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true"><circle cx="12" cy="13" r="8"/><path d="M12 8v5l3 2M9 2h6"/></svg>
                            <span data-timer-rest-label>بدء الراحة · 90ث</span>
                        </button>
                    </div>

                    <!-- إدخال المجموعات -->
                    <div class="space-y-3 mb-4">
                        @for($i = 1; $i <= $dayExercise->default_sets; $i++)
                            @php
                                $savedSet = $todayLogs->where('set_number', $i)->first();
                                $wVal = $savedSet ? $savedSet->weight : '';
                                $rVal = $savedSet ? $savedSet->reps : '';
                            @endphp
                            <div class="gym-workout-set-row">
                                <span class="gym-workout-set-label text-sm font-bold text-gray-300">مجموعة {{ $i }}</span>
                                <label class="block min-w-0">
                                    <span class="sr-only">وزن المجموعة {{ $i }} بالكيلوجرام</span>
                                    <input type="number" step="0.5" inputmode="decimal" name="sets[{{ $i-1 }}][weight]" value="{{ $wVal }}" placeholder="الوزن (كجم)" aria-label="وزن المجموعة {{ $i }} بالكيلوجرام" class="w-full min-w-0 bg-gray-900 border-gray-700 text-white placeholder-gray-400 rounded-xl text-sm focus:border-blue-500">
                                </label>
                                <label class="block min-w-0">
                                    <span class="sr-only">عدات المجموعة {{ $i }}</span>
                                    <input type="number" inputmode="numeric" name="sets[{{ $i-1 }}][reps]" value="{{ $rVal }}" placeholder="العدات" aria-label="عدات المجموعة {{ $i }}" class="w-full min-w-0 bg-gray-900 border-gray-700 text-white placeholder-gray-400 rounded-xl text-sm focus:border-blue-500">
                                </label>
                            </div>
                        @endfor
                    </div>

                    <!-- زر حفظ التمرين الحالي منفصلاً -->
                    <div class="flex justify-stretch sm:justify-end border-t border-gray-700/50 pt-3">
                        <x-ui.submit-button class="min-h-11 w-full sm:w-auto py-2 px-4 sm:px-6 text-xs transition shadow-md">
                            💾 حفظ تمرين {{ $ex->name }}
                        </x-ui.submit-button>
                    </div>
                </form>
            </div>
        @endforeach
        @endif

    </div>
    <script>
    // Bind the rest-setting input immediately, including when a previous JS bundle is cached.
    // The shared workout timer reads the updated button duration on click.
    (() => {
        const setup = () => {
            document.querySelectorAll('[data-timer-exercise]').forEach(card => {
                const field = card.querySelector('[data-timer-exercise-duration]');
                if (!field || field.dataset.gymRestBound) return;
                field.dataset.gymRestBound = '1';
                const id = card.dataset.timerExercise;
                const owner = document.querySelector('[data-gym-timer]')?.dataset.userId;
                const key = owner ? `gym-workout-timer:v1:${owner}:exercise-rest` : null;
                let stored = {};
                try { stored = JSON.parse(localStorage.getItem(key) || '{}') || {}; } catch (_) {}
                const valid = v => v !== '' && Number.isInteger(Number(v)) && Number(v) >= 1 && Number(v) <= 35999;
                if (valid(stored[id])) field.value = String(stored[id]);
                const refresh = () => {
                    const value = valid(field.value) ? Number(field.value) : 90;
                    card.querySelectorAll('[data-timer-rest]').forEach(button => {
                        button.dataset.timerRest = String(value);
                        const label = button.querySelector('[data-timer-rest-label]');
                        if (label) label.textContent = `بدء الراحة · ${value}ث`;
                        button.setAttribute('aria-label', `بدء راحة ${value} ثانية`);
                    });
                    return value;
                };
                const config = card.querySelector('[data-rest-config]');
                const trigger = config?.querySelector('[data-rest-trigger]');
                const panel = config?.querySelector('[data-rest-popover]');
                if (trigger && panel) {
                    const close = () => { panel.hidden = true; trigger.setAttribute('aria-expanded','false'); };
                    const open = () => { panel.hidden = false; trigger.setAttribute('aria-expanded','true'); };
                    trigger.addEventListener('click', () => {
                        const wasOpen = !panel.hidden;
                        document.querySelectorAll('[data-rest-popover]:not([hidden])').forEach(other => {
                            other.hidden = true;
                            other.closest('[data-rest-config]')?.querySelector('[data-rest-trigger]')?.setAttribute('aria-expanded','false');
                        });
                        if (!wasOpen) open();
                    });
                    config.addEventListener('keydown', event => {
                        if (event.key === 'Escape') { close(); trigger.focus(); }
                    });
                    for (const [selector, step] of [['[data-rest-minus]', -5], ['[data-rest-plus]', 5]]) {
                        config.querySelector(selector)?.addEventListener('click', () => {
                            const current = valid(field.value) ? Number(field.value) : 90;
                            field.value = String(Math.min(35999, Math.max(1, current + step)));
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                        });
                    }
                }
                field.addEventListener('input', () => {
                    const value = refresh();
                    if (valid(field.value) && key) {
                        stored[id] = value;
                        try { localStorage.setItem(key, JSON.stringify(stored)); } catch (_) {}
                    }
                });
                field.addEventListener('change', () => {
                    if (!valid(field.value)) field.value = String(valid(stored[id]) ? stored[id] : 90);
                    refresh();
                });
                refresh();
            });
        };
        document.addEventListener('click', event => {
            document.querySelectorAll('[data-rest-config]').forEach(config => {
                if (config.contains(event.target)) return;
                const panel = config.querySelector('[data-rest-popover]');
                const trigger = config.querySelector('[data-rest-trigger]');
                if (panel) panel.hidden = true;
                if (trigger) trigger.setAttribute('aria-expanded','false');
            });
        });
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup);
        else setup();
    })();
    </script>
</x-app-layout>
