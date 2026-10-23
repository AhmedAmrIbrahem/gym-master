<script>
document.addEventListener('DOMContentLoaded', () => {
    const eyeOpen = '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
    const eyeClosed = '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A11.6 11.6 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8"/><path d="M6.1 6.1C3.5 8 2 12 2 12s3.6 7 10 7c1.8 0 3.4-.6 4.7-1.4"/><path d="M10 10a3 3 0 0 0 4 4"/></svg>';

    document.querySelectorAll('input[type="password"]').forEach((input) => {
        if (input.dataset.visibilityToggle) return;
        input.dataset.visibilityToggle = 'true';

        const wrapper = document.createElement('div');
        const computed = window.getComputedStyle(input);
        wrapper.style.position = 'relative';
        wrapper.style.display = 'block';
        wrapper.style.width = '100%';
        wrapper.style.marginTop = computed.marginTop;
        wrapper.style.marginBottom = computed.marginBottom;
        wrapper.style.marginLeft = computed.marginLeft;
        wrapper.style.marginRight = computed.marginRight;
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        input.style.display = 'block';
        input.style.width = '100%';
        input.style.boxSizing = 'border-box';
        input.style.margin = '0';
        input.style.paddingLeft = '2.9rem';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'flex items-center justify-center text-gray-400 hover:text-white focus:outline-none focus:text-blue-400';
        button.style.position = 'absolute';
        button.style.left = '0.75rem';
        button.style.top = '50%';
        button.style.transform = 'translateY(-50%)';
        button.style.zIndex = '10';
        button.style.width = '1.5rem';
        button.style.height = '1.5rem';
        button.style.padding = '0';
        button.style.background = 'transparent';
        button.style.border = '0';
        button.setAttribute('aria-label', 'إظهار كلمة المرور');
        button.setAttribute('aria-pressed', 'false');
        button.innerHTML = eyeOpen;
        button.addEventListener('click', () => {
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.innerHTML = visible ? eyeClosed : eyeOpen;
            button.setAttribute('aria-label', visible ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            button.setAttribute('aria-pressed', String(visible));
        });
        wrapper.appendChild(button);
    });
});
</script>
