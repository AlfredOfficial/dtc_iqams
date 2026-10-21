const updatePasswordToggle = (button, input, visible) => {
    input.type = visible ? 'text' : 'password';
    button.setAttribute('aria-pressed', String(visible));
    button.setAttribute('aria-label', `${visible ? 'Hide' : 'Show'} ${button.dataset.passwordLabel || 'password'}`);
    button.querySelector('[data-password-eye]')?.classList.toggle('hidden', visible);
    button.querySelector('[data-password-eye-slash]')?.classList.toggle('hidden', !visible);
};

export const installPasswordToggles = (document) => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest?.('[data-password-toggle]');
        if (!button) return;

        const input = document.getElementById(button.dataset.passwordTarget);
        if (!input || !input.matches('[data-password-input]')) return;

        const visible = input.type === 'password';
        const value = input.value;
        const selection = typeof input.selectionStart === 'number'
            ? [input.selectionStart, input.selectionEnd, input.selectionDirection]
            : null;

        updatePasswordToggle(button, input, visible);
        input.value = value;

        if (selection && typeof input.setSelectionRange === 'function') {
            try {
                input.setSelectionRange(...selection);
            } catch {
                // Some browsers do not support selection ranges on password inputs.
            }
        }
    });
};
