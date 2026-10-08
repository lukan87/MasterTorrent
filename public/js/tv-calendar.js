(() => {
    document.querySelectorAll('.tvc-quick-options').forEach((form) => {
        form.addEventListener('change', (event) => {
            if (event.target.matches('input[type="checkbox"]')) {
                form.requestSubmit();
            }
        });
    });
})();
