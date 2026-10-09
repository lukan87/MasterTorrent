document.addEventListener('DOMContentLoaded', () => {
    const field = document.getElementById('new-api-token');
    if (!field) return;
    document.querySelector('[data-reveal-api-token]')?.addEventListener('click', (event) => {
        const visible = field.type === 'password';
        field.type = visible ? 'text' : 'password';
        event.currentTarget.textContent = visible ? 'Hide token' : 'Show token';
        event.currentTarget.setAttribute('aria-pressed', String(visible));
    });
    document.querySelector('[data-copy-api-token]')?.addEventListener('click', async () => {
        const status = document.querySelector('[data-copy-status]');
        try { await navigator.clipboard.writeText(field.value); status.textContent = 'Copied.'; }
        catch { status.textContent = 'Show the token and copy it manually.'; }
    });
    // Clear one-time credentials before this page is restored from browser navigation.
    window.addEventListener('pagehide', () => { field.value = ''; });
});
