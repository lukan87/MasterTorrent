(() => {
    const initialize = () => {
        document.querySelectorAll('[data-email-preference-url]').forEach((toggle) => {
            let savedValue = toggle.checked;
            toggle.addEventListener('change', async () => {
                const requestedValue = toggle.checked;
                toggle.disabled = true;
                toggle.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(toggle.dataset.emailPreferenceUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': toggle.dataset.csrf,
                        },
                        body: JSON.stringify({ subscribed: requestedValue }),
                    });

                    if (!response.ok) {
                        throw new Error(response.status === 401 || response.status === 419
                            ? 'Your session has expired. Refresh the page and sign in again before changing your email preference.'
                            : 'We could not save your email preference. Please try again.');
                    }

                    const result = await response.json();
                    if (typeof result.subscribed !== 'boolean' || typeof result.message !== 'string') {
                        throw new Error('We could not confirm your email preference. Please try again.');
                    }
                    savedValue = result.subscribed;
                    toggle.checked = savedValue;
                    Swal.fire({
                        icon: 'success',
                        title: savedValue ? 'Email subscription enabled' : 'Email subscription disabled',
                        text: result.message,
                        confirmButtonText: 'Got it',
                    });
                } catch (error) {
                    toggle.checked = savedValue;
                    Swal.fire({
                        icon: 'error',
                        title: 'Email preference not updated',
                        text: error instanceof TypeError
                            ? 'Could not connect. Please check your connection and try again.'
                            : error.message,
                    });
                } finally {
                    toggle.disabled = false;
                    toggle.removeAttribute('aria-busy');
                }
            });
        });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
})();
