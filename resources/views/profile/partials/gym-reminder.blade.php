<style>
.gym-reminder-card{--rem-bg:#1f2937;--rem-panel:#172131;--rem-line:#37465b;--rem-text:#f1f5f9;--rem-muted:#a5b4c8;background:var(--rem-bg);color:var(--rem-text);border:1px solid var(--rem-line);border-radius:22px;overflow:hidden;box-shadow:0 12px 32px rgba(0,0,0,.08)}
.gym-reminder-header{display:flex;align-items:flex-start;gap:14px;padding:24px;border-bottom:1px solid var(--rem-line);background:linear-gradient(120deg,rgba(37,99,235,.09),transparent)}
.gym-reminder-icon{flex:0 0 46px;width:46px;height:46px;display:grid;place-items:center;border:1px solid rgba(96,165,250,.28);border-radius:15px;background:rgba(59,130,246,.12);color:#93c5fd}
.gym-reminder-heading{flex:1;min-width:0}
.gym-reminder-heading h3{margin:0 0 6px;font-size:18px;line-height:1.6;font-weight:800;color:var(--rem-text);scroll-margin-top:24px}
.gym-reminder-heading p{margin:0;font-size:12px;line-height:1.9;color:var(--rem-muted)}
.gym-reminder-state{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;border:1px solid var(--rem-line);border-radius:999px;padding:6px 10px;font-size:11px;font-weight:700;color:var(--rem-muted);background:var(--rem-panel)}
.gym-reminder-state i{width:6px;height:6px;border-radius:50%;background:#94a3b8}
.gym-reminder-state.is-enabled{color:#6ee7b7;border-color:rgba(52,211,153,.25);background:rgba(16,185,129,.08)}
.gym-reminder-state.is-enabled i{background:#34d399}
.gym-reminder-body{padding:24px;display:grid;gap:20px}
.gym-reminder-recipient{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 16px}
.gym-reminder-caption{display:block;font-size:12px;color:var(--rem-muted);margin-bottom:5px}
.gym-reminder-phone{display:inline-flex;align-items:center;gap:8px;font-size:15px;font-weight:700;color:var(--rem-text)}
.gym-reminder-phone svg{color:#34d399;flex-shrink:0}
.gym-reminder-note{margin:0;font-size:11px;line-height:1.9;color:var(--rem-muted)}
.gym-reminder-form{display:grid;gap:18px;margin:0}
.gym-reminder-toggle{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:16px;border:1px solid var(--rem-line);border-radius:15px;background:var(--rem-panel);cursor:pointer}
.gym-reminder-toggle strong{display:block;font-size:13px;font-weight:800}
.gym-reminder-toggle small{display:block;margin-top:4px;font-size:11px;line-height:1.8;color:var(--rem-muted)}
.gym-reminder-switch{position:relative;display:inline-flex;flex:0 0 44px;width:44px;height:26px}
.gym-reminder-switch input{position:absolute;opacity:0;width:1px;height:1px}
.gym-reminder-track{position:absolute;inset:0;border:1px solid #536176;border-radius:999px;background:#455268;transition:background .18s,border-color .18s}
.gym-reminder-track::after{content:'';position:absolute;left:3px;top:3px;width:18px;height:18px;border-radius:50%;background:white;box-shadow:0 1px 4px rgba(0,0,0,.2);transition:transform .18s}
.gym-reminder-switch input:checked+.gym-reminder-track{background:#2563eb;border-color:#3b82f6}
.gym-reminder-switch input:checked+.gym-reminder-track::after{transform:translateX(18px)}
.gym-reminder-switch input:focus-visible+.gym-reminder-track{outline:3px solid #93c5fd;outline-offset:3px}
.gym-reminder-fields{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.4fr);gap:16px}
.gym-reminder-week{display:grid;gap:9px;min-width:0;margin:0;padding:0;border:0}
.gym-reminder-day{display:grid;grid-template-columns:minmax(0,1fr) 180px;align-items:center;gap:14px;padding:12px 14px;min-width:0;border:1px solid var(--rem-line);border-radius:13px;background:var(--rem-panel)}
.gym-reminder-day label{display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;font-weight:700;min-height:36px}
.gym-reminder-day input[type="checkbox"]{width:18px;height:18px;border-radius:5px;color:#2563eb;accent-color:#2563eb}
.gym-reminder-card .gym-reminder-day input.gym-reminder-control{width:100%;min-width:0;max-width:100%;height:48px;padding:10px 12px;font-size:16px;font-weight:600;line-height:1.5;letter-spacing:normal;text-align:left;direction:ltr;box-sizing:border-box}
.gym-reminder-day input[type="time"]::-webkit-datetime-edit{padding:0}
.gym-reminder-day input[type="time"]::-webkit-date-and-time-value{text-align:left;min-height:1.5em}
.gym-reminder-cairo{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:13px 15px;border-radius:13px;background:rgba(59,130,246,.08);border:1px solid rgba(96,165,250,.22);font-size:12px}
.gym-reminder-field{min-width:0}
.gym-reminder-field label{display:block;margin-bottom:9px;font-size:12px;font-weight:700}
.gym-reminder-card .gym-reminder-control{display:block;width:100%;height:54px;border:1px solid var(--rem-line);border-radius:13px;padding:10px 14px;background:var(--rem-panel);color:var(--rem-text);font:inherit;font-size:13px;box-sizing:border-box;color-scheme:dark}
.gym-reminder-card input.gym-reminder-control{font-size:21px;font-weight:700;text-align:center;direction:ltr}
.gym-reminder-control:focus{outline:2px solid #60a5fa;outline-offset:2px}
.gym-reminder-field .gym-reminder-note{margin-top:7px}
.gym-reminder-consent{display:flex;align-items:flex-start;gap:10px;padding:14px 16px;border:1px solid var(--rem-line);border-radius:13px;font-size:12px;line-height:1.9;cursor:pointer}
.gym-reminder-consent input{flex:0 0 17px;width:17px;height:17px;margin-top:4px;border-radius:5px;border-color:#64748b;color:#2563eb;accent-color:#2563eb}
.gym-reminder-consent small{display:block;font-size:11px;color:var(--rem-muted)}
.gym-reminder-consent-saved{display:block;margin-top:5px;color:#6ee7b7;font-size:11px}
.gym-reminder-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;padding-top:2px}
.gym-reminder-save{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:44px;padding:11px 20px;background:#2563eb;color:#fff;border:1px solid #3b82f6;border-radius:12px;font-family:inherit;font-size:12px;font-weight:800;cursor:pointer;transition:background .18s}
.gym-reminder-save:hover{background:#1d4ed8}
.gym-reminder-save:focus-visible{outline:3px solid #93c5fd;outline-offset:3px}
.gym-reminder-alert{margin:0;padding:12px 14px;border-radius:12px;font-size:12px;line-height:1.9;background:rgba(245,158,11,.09);color:#fcd34d;border:1px solid rgba(245,158,11,.25)}
.gym-reminder-errors{padding:12px 14px;border:1px solid rgba(248,113,113,.3);background:rgba(239,68,68,.08);border-radius:12px;font-size:12px;line-height:1.9;color:#fca5a5;list-style:inside;margin:0}
html[data-theme="light"] .gym-reminder-card{--rem-bg:#fff;--rem-panel:#f5f7fb;--rem-line:#dbe3ef;--rem-text:#172033;--rem-muted:#64748b;box-shadow:0 10px 30px rgba(30,64,175,.05)}
html[data-theme="light"] .gym-reminder-icon{color:#2563eb}
html[data-theme="light"] .gym-reminder-state.is-enabled,html[data-theme="light"] .gym-reminder-consent-saved{color:#047857}
html[data-theme="light"] .gym-reminder-phone svg{color:#059669}
html[data-theme="light"] .gym-reminder-card .gym-reminder-control{color-scheme:light}
html[data-theme="light"] .gym-reminder-alert{color:#92400e}
html[data-theme="light"] .gym-reminder-errors{color:#b91c1c}
@media(max-width:560px){
.gym-reminder-header{padding:18px;gap:10px;flex-wrap:wrap}
.gym-reminder-icon{width:40px;height:40px;flex-basis:40px;border-radius:12px}
.gym-reminder-heading{flex-basis:calc(100% - 50px)}
.gym-reminder-heading h3{font-size:16px}
.gym-reminder-state{margin-inline-start:50px}
.gym-reminder-body{padding:18px;gap:17px}
.gym-reminder-fields{grid-template-columns:minmax(0,1fr)}
.gym-reminder-day{grid-template-columns:minmax(0,1fr);padding:12px;gap:9px}
.gym-reminder-day label{min-height:30px}
.gym-reminder-footer{flex-direction:column;align-items:stretch}
.gym-reminder-footer .gym-reminder-note{text-align:center}
}
@media(prefers-reduced-motion:reduce){.gym-reminder-track,.gym-reminder-track::after,.gym-reminder-save{transition:none}}
</style>

<section class="gym-reminder-card" aria-labelledby="gym-reminder-title">
    <header class="gym-reminder-header">
        <span class="gym-reminder-icon" aria-hidden="true">
            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
        </span>
        <div class="gym-reminder-heading">
            <h3 id="gym-reminder-title">تذكير موعد الجيم على واتساب</h3>
            <p>اختار أيام الجيم، وحدد موعدًا مناسبًا لكل يوم.</p>
        </div>
        <span class="gym-reminder-state {{ $gymReminder?->enabled ? 'is-enabled' : '' }}" aria-label="حالة التذكير المحفوظة">
            <i aria-hidden="true"></i>المحفوظ: {{ $gymReminder?->enabled ? 'مفعّل' : 'متوقف' }}
        </span>
    </header>

    <div class="gym-reminder-body">
        <div class="gym-reminder-recipient">
            <div>
                <span class="gym-reminder-caption">رقم استقبال التذكير</span>
                <span class="gym-reminder-phone">
                    <svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4-.9L3 21l1.9-5.5a9 9 0 0 1-.9-4A8.5 8.5 0 0 1 12.5 3H13a8.5 8.5 0 0 1 8 8v.5Z"/><path d="M8.5 8.5c0 3.5 3.5 7 7 7"/></svg>
                    <bdi>{{ $user->phone ?: 'لا يوجد رقم مسجل' }}</bdi>
                </span>
            </div>
            <p class="gym-reminder-note">{{ $user->phone ? 'على رقمك المسجل في الحساب، في محادثة خاصة بك.' : 'اطلب من الإدارة إضافة رقمك قبل تفعيل التذكير.' }}</p>
        </div>
        @if(! config('gym_reminders.enabled'))
            <p class="gym-reminder-alert">خدمة الإرسال متوقفة حاليًا من الإدارة؛ يمكنك حفظ إعداداتك.</p>
        @endif
        @if($errors->gymReminder->any())
            <ul role="alert" class="gym-reminder-errors">
                @foreach($errors->gymReminder->all() as $message)<li>{{ $message }}</li>@endforeach
            </ul>
        @endif

        <form method="post" action="{{ route('profile.gym-reminder.update') }}" class="gym-reminder-form">
            @csrf
            @method('put')
            <input type="hidden" name="enabled" value="0">
            <label class="gym-reminder-toggle" for="gym-reminder-enabled">
                <span><strong>تفعيل جدول التذكيرات</strong><small>أوقف الجدول كله أو اختَر الأيام التي تريد استقبال التذكير فيها.</small></span>
                <span class="gym-reminder-switch">
                    <input id="gym-reminder-enabled" type="checkbox" role="switch" name="enabled" value="1" @checked(old('enabled', $gymReminder?->enabled ?? false))>
                    <span class="gym-reminder-track" aria-hidden="true"></span>
                </span>
            </label>
            <div class="gym-reminder-cairo"><strong>توقيت مصر — القاهرة</strong><span class="gym-reminder-note">ثابت لكل المواعيد، ويُراعى التوقيت الصيفي تلقائيًا.</span></div>
            @php($week = $gymReminder?->scheduleForWeek() ?? array_fill(0, 7, '18:00'))
            <fieldset class="gym-reminder-week">
                <legend class="gym-reminder-caption" style="margin-bottom:10px">أيام الإرسال · ألغِ علامة أي يوم لا تريد رسالة فيه</legend>
                @foreach(\App\Models\GymReminder::DAYS as $day => $label)
                    <div class="gym-reminder-day">
                        <input type="hidden" name="days[{{ $day }}][enabled]" value="0">
                        <label for="gym-day-{{ $day }}">
                            <input id="gym-day-{{ $day }}" type="checkbox" name="days[{{ $day }}][enabled]" value="1" @checked(old('days.'.$day.'.enabled', array_key_exists($day, $week)))>
                            {{ $label }}
                        </label>
                        <input type="time" name="days[{{ $day }}][time]" required value="{{ old('days.'.$day.'.time', $week[$day] ?? '18:00') }}" class="gym-reminder-control" aria-label="موعد تذكير {{ $label }} بتوقيت القاهرة">
                    </div>
                @endforeach
            </fieldset>
            <p class="gym-reminder-note">اختر موعدًا لاحقًا لوقت الحفظ. تغيير موعد اليوم إلى وقت لاحق يسمح بتذكير جديد؛ حفظ نفس الإعدادات لا يعيد الإرسال.</p>
            <label class="gym-reminder-consent" for="gym-reminder-opt-in">
                <input id="gym-reminder-opt-in" type="checkbox" name="opt_in" value="1" @checked(old('opt_in', false))>
                <span>
                    أوافق على استقبال تذكير الجيم عبر واتساب على رقمي المسجل.
                    <small>علّم الخانة عند التفعيل أو تعديل الأيام والمواعيد. الإيقاف لا يحتاج موافقة.</small>
                    @if($gymReminder?->enabled && $gymReminder?->consented_at && $gymReminder?->consent_phone === \App\Services\GymReminders\PhoneNumber::normalize($user->phone))
                        <span class="gym-reminder-consent-saved">✓ موافقتك محفوظة للرقم الحالي؛ الخانة ترجع فارغة بعد الحفظ.</span>
                    @endif
                </span>
            </label>
            <footer class="gym-reminder-footer">
                <button class="gym-reminder-save" type="submit">
                    <svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                    حفظ إعدادات التذكير
                </button>
                <p class="gym-reminder-note">تغيير رقم الحساب يحتاج موافقة جديدة.</p>
            </footer>
        </form>
    </div>
</section>
