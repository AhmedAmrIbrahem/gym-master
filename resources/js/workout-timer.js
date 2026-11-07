/** Shared, per-user workout timer. No server-side changes required. */
function initWorkoutTimer() {
    const root = document.querySelector('[data-gym-timer]');
    if (!root) return;
    const key = `gym-workout-timer:v1:${root.dataset.userId}`;
    const prefKey = `${key}:prefs`;
    const $ = (selector) => root.querySelector(selector);
    const buttons = [...document.querySelectorAll('[data-timer-open]')];
    const timeEls = [...document.querySelectorAll('[data-timer-compact-time]')];
    const miniEls = [...document.querySelectorAll('[data-timer-mini]')];
    const defaultState = () => ({ mode: 'timer', duration: 90, running: false, elapsed: 0, endAt: null, startedAt: null, finished: false, updatedAt: Date.now() });
    const safeRead = (k) => { try { return JSON.parse(localStorage.getItem(k) || 'null'); } catch { return null; } };
    let state = { ...defaultState(), ...safeRead(key) };
    let prefs = { sound: false, ...safeRead(prefKey) };
    let exerciseRest = safeRead(`${key}:exercise-rest`) || {};
    let wasRunning = !!state.running;
    let audioCtx = null;
    const now = () => Date.now();
    const totalSeconds = () => state.mode === 'timer'
        ? (state.running ? Math.max(0, Math.ceil((state.endAt - now()) / 1000)) : Math.max(0, Math.ceil((state.duration * 1000 - state.elapsed) / 1000)))
        : Math.floor((state.elapsed + (state.running ? now() - state.startedAt : 0)) / 1000);
    const format = (seconds) => {
        const h = Math.floor(seconds / 3600);
        const m = String(Math.floor(seconds % 3600 / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        return h ? `${h}:${m}:${s}` : `${m}:${s}`;
    };
    const persist = () => { state.updatedAt = now(); try { localStorage.setItem(key, JSON.stringify(state)); } catch {} };
    const savePrefs = () => { try { localStorage.setItem(prefKey, JSON.stringify(prefs)); } catch {} };
    const unlockSound = () => {
        if (!prefs.sound) return;
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (Ctx && !audioCtx) audioCtx = new Ctx();
            audioCtx?.resume?.().catch(() => {});
        } catch {}
    };
    const soundFinished = () => {
        if (!prefs.sound) return;
        try {
            unlockSound();
            if (!audioCtx) return;
            [0, 0.2, 0.4].forEach((delay) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine'; osc.frequency.value = 760;
                gain.gain.setValueAtTime(0.0001, audioCtx.currentTime + delay);
                gain.gain.exponentialRampToValueAtTime(0.12, audioCtx.currentTime + delay + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + delay + 0.14);
                osc.connect(gain); gain.connect(audioCtx.destination);
                osc.start(audioCtx.currentTime + delay);
                osc.stop(audioCtx.currentTime + delay + 0.15);
            });
        } catch {}
    };
    const finish = () => {
        if (!state.running || state.mode !== 'timer' || totalSeconds() > 0) return;
        state.running = false; state.elapsed = state.duration * 1000; state.endAt = null; state.finished = true;
        persist(); soundFinished(); render();
    };
    const render = () => {
        const remaining = totalSeconds();
        const active = state.running;
        const warning = state.mode === 'timer' && remaining <= 10 && remaining > 0 && active;
        const display = $('[data-timer-display]');
        display.textContent = format(remaining);
        // Keep long elapsed times inside the ring without resizing the ring itself.
        const chars = display.textContent.length;
        display.style.fontSize = chars >= 11 ? '1.15rem' : chars >= 9 ? '1.4rem' : chars >= 7 ? '1.8rem' : '2.2rem';
        $('[data-timer-hint]').textContent = state.finished ? 'انتهى المؤقت' : state.mode === 'timer' ? 'الوقت المتبقي' : 'الوقت المنقضي';
        $('[data-timer-done]').textContent = 'انتهى المؤقت ✓';
        $('[data-timer-play-label]').textContent = active ? 'إيقاف مؤقت' : (state.elapsed > 0 && !state.finished ? 'استكمال' : 'بدء');
        $('[data-timer-play-icon]').textContent = active ? 'Ⅱ' : '▶';
        $('[data-timer-done]').hidden = !state.finished;
        $('[data-timer-ring]').style.strokeDashoffset = String(276.46 * (1 - (state.mode === 'timer' ? remaining / state.duration : (remaining % 60) / 60)));
        $('[data-timer-ring]').style.stroke = warning ? '#f59e0b' : state.mode === 'timer' ? '#3b82f6' : '#10b981';
        $('[data-timer-display]').style.color = warning ? '#fbbf24' : '';
        root.querySelectorAll('[data-timer-mode]').forEach(el => {
            const selected = el.dataset.timerMode === state.mode;
            el.classList.toggle('bg-blue-600', selected);
            el.classList.toggle('text-white', selected);
            el.classList.toggle('text-gray-400', !selected);
            el.setAttribute('aria-pressed', String(selected));
        });
        $('[data-timer-presets]').hidden = state.mode !== 'timer';
        $('[data-timer-custom]').hidden = state.mode !== 'timer';
        root.querySelectorAll('[data-timer-preset]').forEach(el => {
            el.classList.toggle('border-blue-500', Number(el.dataset.timerPreset) === state.duration);
            el.classList.toggle('text-blue-400', Number(el.dataset.timerPreset) === state.duration);
        });
        $('[data-timer-sound]').checked = prefs.sound;
        // Show the time on mobile as well as desktop. Keep it shown when paused.
        const showMini = active || state.finished || state.elapsed > 0;
        timeEls.forEach(el => { el.textContent = state.finished ? '00:00' : format(remaining); });
        miniEls.forEach(el => {
            el.hidden = !showMini;
            el.style.display = showMini ? 'inline-block' : 'none';
        });
        buttons.forEach(button => {
            button.dataset.timerActive = showMini ? 'true' : 'false';
            button.setAttribute('aria-label', `فتح الساعة والمؤقت${showMini ? `، ${format(remaining)}` : ''}`);
        });
        document.querySelectorAll('[data-timer-compact-status]').forEach(el => { el.textContent = state.mode === 'timer' ? 'مؤقت الراحة' : 'ساعة التمرين'; });
        // Reuse the same source of truth for the header, overlay and dashboard ring.
        const dashboard = document.querySelector('[data-timer-dashboard]');
        if (dashboard) {
            const visible = active || state.finished || state.elapsed > 0;
            const paused = !active && !state.finished && state.elapsed > 0;
            dashboard.dataset.status = state.finished ? 'finished' : active ? 'running' : paused ? 'paused' : 'idle';
            dashboard.querySelector('[data-timer-dash-live]').hidden = !visible;
            dashboard.querySelector('[data-timer-dash-idle]').hidden = visible;
            dashboard.querySelector('[data-timer-dash-toggle]').hidden = !visible || state.finished;
            const timeText = state.finished ? '00:00' : format(remaining);
            const clock = dashboard.querySelector('[data-timer-dash-time]');
            clock.textContent = timeText;
            // Reduce digits only, never the ring itself.
            clock.style.fontSize = timeText.length >= 10 ? '15px' : timeText.length >= 8 ? '19px' : timeText.length >= 7 ? '22px' : '26px';
            dashboard.querySelector('[data-timer-dash-status]').textContent = state.finished ? 'انتهى الوقت' : active ? (state.mode === 'timer' ? 'المؤقت يعمل الآن' : 'ساعة الإيقاف تعمل الآن') : paused ? 'متوقف مؤقتًا' : 'جاهز للاستخدام في أي وقت';
            dashboard.querySelector('[data-timer-dash-caption]').textContent = state.finished ? 'انتهى الوقت' : paused ? 'متوقف مؤقتًا' : state.mode === 'timer' ? 'الوقت المتبقي' : 'الوقت المنقضي';
            dashboard.querySelector('[data-timer-dash-toggle-text]').textContent = active ? 'إيقاف مؤقت' : 'متابعة';
            dashboard.querySelector('[data-timer-dash-open-text]').textContent = visible ? 'فتح الساعة' : 'فتح المؤقت';
            // Timer arc represents remaining time; stopwatch arc fills once per minute.
            const fraction = state.mode === 'timer'
                ? (state.duration > 0 ? remaining / state.duration : 0)
                : ((remaining % 60) / 60);
            const progress = Math.max(0, Math.min(1, fraction));
            const ring = dashboard.querySelector('[data-timer-dash-ring]');
            ring.style.strokeDashoffset = String(326.726 * (1 - progress));
            ring.style.stroke = paused ? '#f59e0b' : state.finished ? '#10b981' : state.mode === 'stopwatch' ? '#10b981' : '#3b82f6';
        }
    };
    const startPause = () => {
        unlockSound();
        if (state.running) {
            state.elapsed = state.mode === 'timer' ? Math.max(0, state.duration * 1000 - (state.endAt - now())) : state.elapsed + (now() - state.startedAt);
            state.running = false; state.endAt = null; state.startedAt = null;
        } else {
            if (state.finished || (state.mode === 'timer' && state.elapsed >= state.duration * 1000)) { state.elapsed = 0; state.finished = false; }
            state.running = true;
            if (state.mode === 'timer') state.endAt = now() + state.duration * 1000 - state.elapsed;
            else state.startedAt = now();
        }
        persist(); render();
    };
    const reset = () => {
        state.running = false; state.elapsed = 0; state.endAt = null; state.startedAt = null; state.finished = false;
        persist(); render();
    };
    const configure = (mode, duration = state.duration, autoStart = false) => {
        state = { ...defaultState(), mode, duration };
        if (autoStart) { unlockSound(); state.running = true; if (mode === 'timer') state.endAt = now() + duration * 1000; else state.startedAt = now(); }
        persist(); render();
    };
    const overlay = $('[data-timer-overlay]');
    const panel = $('[data-timer-panel]');
    let previousFocus;
    const open = () => {
        previousFocus = document.activeElement;
        overlay.hidden = false;
        document.body.classList.add('overflow-hidden', 'gym-timer-open');
        panel.focus();
        render();
    };
    const close = () => {
        overlay.hidden = true;
        document.body.classList.remove('overflow-hidden', 'gym-timer-open');
        previousFocus?.focus?.();
    };
    document.querySelectorAll('[data-timer-open]').forEach(el => el.addEventListener('click', open));
    root.querySelectorAll('[data-timer-close]').forEach(el => el.addEventListener('click', close));
    overlay.addEventListener('click', (event) => { if (event.target === overlay) close(); });
    document.addEventListener('keydown', (event) => {
        if (overlay.hidden) return;
        if (event.key === 'Escape') close();
        if (event.key === 'Tab') {
            const focusables = [...panel.querySelectorAll('button:not([disabled]),input:not([disabled])')].filter(el => el.getClientRects().length);
            if (!focusables.length) return;
            const first = focusables[0], last = focusables[focusables.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    $('[data-timer-play]').addEventListener('click', startPause);
    document.querySelector('[data-timer-dash-toggle]')?.addEventListener('click', startPause);
    $('[data-timer-reset]').addEventListener('click', reset);
    root.querySelectorAll('[data-timer-mode]').forEach(el => el.addEventListener('click', () => configure(el.dataset.timerMode)));
    root.querySelectorAll('[data-timer-preset]').forEach(el => el.addEventListener('click', () => configure('timer', Number(el.dataset.timerPreset))));
    $('[data-timer-custom-input]').addEventListener('change', event => {
        const seconds = Math.round(Number(event.target.value));
        if (!Number.isFinite(seconds) || seconds < 1 || seconds > 35999) return;
        configure('timer', seconds);
    });
    $('[data-timer-sound]').addEventListener('change', event => { prefs.sound = event.target.checked; savePrefs(); unlockSound(); });
    // Each exercise's input is the source of truth for all of its rest buttons.
    const restStorageKey = `${key}:exercise-rest`;
    const validRest = (value) => value !== '' && Number.isInteger(Number(value)) && Number(value) >= 1 && Number(value) <= 35999;
    document.querySelectorAll('[data-timer-exercise]').forEach(exercise => {
        const exerciseId = exercise.dataset.timerExercise;
        const field = exercise.querySelector('[data-timer-exercise-duration]');
        if (!field || !exerciseId) return;
        if (validRest(exerciseRest[exerciseId])) field.value = String(exerciseRest[exerciseId]);
        const updateButtons = () => {
            const value = validRest(field.value) ? Number(field.value) : 90;
            exercise.querySelectorAll('[data-timer-rest]').forEach(button => {
                button.dataset.timerRest = String(value);
                const label = button.querySelector('[data-timer-rest-label]');
                if (label) label.textContent = `بدء الراحة · ${value}ث`;
                button.setAttribute('aria-label', `بدء راحة ${value} ثانية`);
            });
        };
        const saveRest = () => {
            if (!validRest(field.value)) return;
            exerciseRest[exerciseId] = Number(field.value);
            try { localStorage.setItem(restStorageKey, JSON.stringify(exerciseRest)); } catch {}
        };
        field.addEventListener('input', () => { updateButtons(); saveRest(); });
        field.addEventListener('change', () => {
            if (!validRest(field.value)) field.value = String(validRest(exerciseRest[exerciseId]) ? exerciseRest[exerciseId] : 90);
            updateButtons(); saveRest();
        });
        updateButtons();
    });
    document.querySelectorAll('[data-timer-rest]').forEach(button => button.addEventListener('click', () => {
        const field = button.closest('[data-timer-exercise]')?.querySelector('[data-timer-exercise-duration]');
        const raw = field ? field.value : button.dataset.timerRest;
        const seconds = validRest(raw) ? Number(raw) : 90;
        button.dataset.timerRest = String(seconds);
        configure('timer', seconds, true);
    }));
    window.addEventListener('storage', event => {
        if (event.key === key) { state = { ...defaultState(), ...safeRead(key) }; render(); }
        if (event.key === prefKey) { prefs = { sound: false, ...safeRead(prefKey) }; render(); }
    });
    // Recover accurate time even if the browser throttles hidden tabs or a page reloads.
    const tick = () => {
        if (state.running && state.mode === 'timer' && state.endAt <= now()) finish();
        else render();
    };
    if (state.running && state.mode === 'timer' && state.endAt <= now()) {
        // An expired timer restored after navigation is complete without replaying a stale alert.
        state.running = false; state.elapsed = state.duration * 1000; state.endAt = null; state.finished = true; persist();
    }
    render();
    setInterval(tick, 250);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) tick(); });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initWorkoutTimer);
else initWorkoutTimer();
