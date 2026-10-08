(() => {
    'use strict';
    document.querySelectorAll('[data-mm-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm(form.dataset.mmConfirm)) event.preventDefault();
        });
    });
    const form = document.getElementById('massMessageForm');
    if (!form) return;
    const preview = document.getElementById('recipientPreview');
    const previewButton = document.getElementById('previewRecipients');
    const sendButton = document.getElementById('sendBroadcast');
    const classes = [...form.querySelectorAll('[name="user_class[]"]')];
    let submitting = false;
    let audienceVersion = 0;
    const invalidatePreview = () => {
        audienceVersion++;
        preview.textContent = 'Audience changed. Preview the updated recipient count.';
    };
    classes.forEach(input => input.addEventListener('change', invalidatePreview));
    form.querySelectorAll('[data-mm-select]').forEach(button => button.addEventListener('click', () => {
        classes.forEach(input => { input.checked = button.dataset.mmSelect === 'all'; });
        invalidatePreview();
    }));
    async function getCount() {
        if (!classes.some(input => input.checked)) throw new Error('Choose at least one recipient class.');
        const version = audienceVersion;
        const response = await fetch(form.dataset.previewUrl, {
            method: 'POST',
            headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value},
            body: new FormData(form),
        });
        const data = await response.json();
        if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || 'Unable to preview recipients. Please try again.');
        if (version !== audienceVersion) throw new Error('The audience changed. Please preview again.');
        preview.textContent = `${Number(data.recipients).toLocaleString()} accounts selected for this broadcast.`;
        return data.recipients;
    }
    previewButton.addEventListener('click', async () => {
        previewButton.disabled = true;
        preview.textContent = 'Counting recipients…';
        try { await getCount(); }
        catch (error) { preview.textContent = error.message; }
        finally { previewButton.disabled = false; }
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submitting) return;
        submitting = true;
        sendButton.disabled = true;
        let sent = false;
        try {
            const count = await getCount();
            if (count < 1) throw new Error('No accounts match the selected classes.');
            const sender = document.getElementById('sendAsSystem').checked ? 'System' : 'your staff account';
            if (window.confirm(`Send this broadcast to ${Number(count).toLocaleString()} accounts as ${sender}?`)) {
                HTMLFormElement.prototype.submit.call(form);
                sent = true;
            }
        } catch (error) { preview.textContent = error.message; }
        finally { if (!sent) { submitting = false; sendButton.disabled = false; } }
    });
})();
