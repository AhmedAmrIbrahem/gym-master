<div id="gym-gentle-reminder" class="gym-gentle-reminder" hidden aria-live="off" role="status">
    <div class="gym-gentle-reminder__symbol" aria-hidden="true">✦</div>
    <div class="gym-gentle-reminder__copy">
        <span class="gym-gentle-reminder__eyebrow">تذكير لطيف</span>
        <p data-reminder-text class="gym-gentle-reminder__text"></p>
    </div>
    <button type="button" data-reminder-dismiss class="gym-gentle-reminder__close" aria-label="إغلاق التذكير" title="إغلاق">×</button>
    <button type="button" data-reminder-disable class="gym-gentle-reminder__disable" title="إيقاف التذكيرات حتى إغلاق المتصفح">إيقاف مؤقت</button>
</div>
<style>
/* Dashboard cards are scoped. Default dark palette remains intact. */
.gym-kpi-grid {align-items:stretch}
.gym-kpi-grid > .gym-kpi-card {min-height:200px; height:100%; gap:18px; transition:border-color .2s, box-shadow .2s, transform .2s}
.gym-kpi-grid > .gym-kpi-card > :first-child {min-height:76px}
.gym-kpi-grid > .gym-kpi-card > :last-child {margin-top:auto}
.gym-kpi-grid .gym-kpi-card h4 {line-height:1.45; overflow-wrap:anywhere}
.gym-kpi-grid .gym-kpi-card a, .gym-kpi-grid .gym-kpi-card button {min-height:36px}
@media(min-width:1280px){.gym-kpi-grid > .gym-kpi-card {min-height:210px}}
@media(max-width:639px){.gym-kpi-grid {gap:12px}.gym-kpi-grid > .gym-kpi-card{min-height:168px;padding:17px}.gym-kpi-grid > .gym-kpi-card > :first-child {min-height:65px}}
html[data-theme="light"] .gym-kpi-grid > .gym-kpi-card {background:#fff !important;background-image:linear-gradient(145deg,#fff 0%,#f8fbff 100%) !important;border-color:#dce6f1 !important;box-shadow:0 7px 20px rgba(16,35,64,.045) !important}
html[data-theme="light"] .gym-kpi-grid > .gym-kpi-card:hover {border-color:#bcd1ef !important}
/* Quiet reminders, avoiding the floating coach in the bottom corner */
.gym-gentle-reminder {position:fixed;top:78px;left:18px;z-index:90;width:min(340px,calc(100vw - 36px));display:grid;grid-template-columns:32px 1fr 26px;gap:10px;align-items:center;padding:13px 14px;border-radius:16px;background:#202c40;border:1px solid #46566d;color:#e9f2ff;box-shadow:0 12px 32px rgba(0,0,0,.16);font-family:Cairo,sans-serif;direction:rtl;opacity:1;transform:translateY(0);transition:opacity .22s,transform .22s}
.gym-gentle-reminder[hidden] {display:none !important}
.gym-gentle-reminder__symbol {display:grid;place-items:center;width:32px;height:32px;border-radius:11px;background:#334764;color:#c5d9ff;font-size:20px}
.gym-gentle-reminder__eyebrow {font-size:10px;opacity:.7}
.gym-gentle-reminder__text {font-size:13px;line-height:1.75;font-weight:700;margin:2px 0 0}
.gym-gentle-reminder__close {align-self:start;border:0;background:transparent;color:inherit;font-size:23px;line-height:1;cursor:pointer;padding:1px 5px}
.gym-gentle-reminder__disable {grid-column:2/-1;justify-self:start;background:transparent;border:0;color:inherit;opacity:.7;font-size:10px;cursor:pointer;text-decoration:underline}
html[data-theme="light"] .gym-gentle-reminder {background:#fff;border-color:#d7e2f2;color:#273c59;box-shadow:0 12px 30px rgba(35,57,91,.11)}
html[data-theme="light"] .gym-gentle-reminder__symbol {background:#edf3ff;color:#2863cc}
@media(max-width:639px){.gym-gentle-reminder{top:69px;left:10px;width:min(310px,calc(100vw - 20px));padding:11px;z-index:90}}
@media(prefers-reduced-motion:reduce){.gym-gentle-reminder{transition:none}}
</style>
<script>
(function () {
    const reminder = document.getElementById('gym-gentle-reminder');
    if (!reminder) return;
    const phrases = ['اللهم صلِّ وسلم على نبينا محمد', 'سبحان الله وبحمده', 'الحمد لله', 'أستغفر الله', 'لا إله إلا الله'];
    let timer, hideTimer, index = 0;
    let disabled = false;
    try { disabled = sessionStorage.getItem('gym-reminders-disabled') === '1'; } catch (_) {}
    const hide = () => { reminder.hidden = true; clearTimeout(hideTimer); };
    const schedule = (delay) => { clearTimeout(timer); if (!disabled) timer = setTimeout(show, delay); };
    function show() {
        if (document.hidden || document.activeElement?.matches('input,textarea,select,[contenteditable="true"]') || document.querySelector('[role="dialog"]:not([hidden])')) {
            schedule(60 * 1000);
            return;
        }
        reminder.querySelector('[data-reminder-text]').textContent = phrases[index++ % phrases.length];
        reminder.hidden = false;
        hideTimer = setTimeout(hide, 8500);
        schedule(7.5 * 60 * 1000);
    }
    reminder.querySelector('[data-reminder-dismiss]').addEventListener('click', hide);
    reminder.querySelector('[data-reminder-disable]').addEventListener('click', () => {
        disabled = true; clearTimeout(timer); hide();
        try { sessionStorage.setItem('gym-reminders-disabled','1'); } catch (_) {}
    });
    window.addEventListener('pagehide', () => {clearTimeout(timer);clearTimeout(hideTimer)});
    if (!disabled) schedule(1 * 60 * 1000);
})();
</script>
