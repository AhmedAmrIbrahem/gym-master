

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


// Theme selection is UI-only. Dark stays the default for existing users.
function syncGymThemeButtons() {
    const isLight = document.documentElement.dataset.theme === 'light';
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(isLight));
        button.setAttribute('aria-label', isLight ? 'تفعيل الوضع الداكن' : 'تفعيل الوضع الفاتح');
        const label = button.querySelector('.theme-label');
        if (label) label.textContent = isLight ? 'الوضع الداكن' : 'الوضع الفاتح';
    });
}

document.addEventListener('DOMContentLoaded', syncGymThemeButtons);
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-theme-toggle]');
    if (!button) return;
    const next = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
    document.documentElement.dataset.theme = next;
    try { localStorage.setItem('gym-theme', next); } catch (error) { /* Private mode */ }
    syncGymThemeButtons();
});
