document.addEventListener('DOMContentLoaded', () => {
    const chat = document.getElementById('chatBody');
    if (!chat) return;
    const dialog = document.getElementById('messageDialog');
    const form = document.getElementById('messageActionForm');
    const editor = document.getElementById('messageEditBody');
    const confirmButton = document.getElementById('messageActionConfirm');
    const error = document.getElementById('messageActionError');
    let action;
    let noticeTimer;
    const notify = text => {
        const notice = document.getElementById('messengerNotice');
        notice.textContent = text;
        notice.hidden = false;
        clearTimeout(noticeTimer);
        noticeTimer = setTimeout(() => { notice.hidden = true; }, 4500);
    };
    chat.addEventListener('click', event => {
        const button = event.target.closest('.edit-msg, .delete-msg');
        if (!button) return;
        const bubble = button.closest('.ms-msg-bubble');
        action = { button, bubble, deleting: button.classList.contains('delete-msg') };
        dialog.classList.toggle('is-delete', action.deleting);
        document.getElementById('messageDialogTitle').textContent = action.deleting ? 'Delete this message?' : 'Edit message';
        document.getElementById('messageDialogDescription').textContent = action.deleting ? 'This permanently removes this message for both participants.' : 'Update your message. Your formatting is preserved.';
        editor.hidden = action.deleting;
        editor.required = !action.deleting;
        editor.value = bubble.dataset.body;
        confirmButton.textContent = action.deleting ? 'Delete message' : 'Save changes';
        error.textContent = '';
        dialog.showModal();
    });
    document.getElementById('messageActionCancel').addEventListener('click', () => dialog.close());
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const current = action;
        if (!current.deleting && !editor.value.trim()) { error.textContent = 'Write a message before saving.'; return; }
        confirmButton.disabled = true;
        try {
            const response = await fetch(current.button.dataset.url, {
                method: current.deleting ? 'DELETE' : 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                ...(current.deleting ? {} : { body: JSON.stringify({ body: editor.value }) })
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.errors?.body?.[0] || data.error || data.message || 'Could not save this change. Please try again.');
            if (current.deleting) {
                current.bubble.closest('.ms-msg-row').remove();
                // Refresh conversation previews and unread badges after deletion.
                window.location.reload();
            } else {
                current.bubble.querySelector('.ms-msg-content').innerHTML = data.html;
                current.bubble.dataset.body = data.body;
                notify('Message updated');
            }
            dialog.close();
        } catch (failure) {
            error.textContent = failure.message || 'Connection failed. Please try again.';
        } finally {
            confirmButton.disabled = false;
        }
    });
    let polling = false;
    async function refreshReceipts() {
        if (polling || document.hidden || dialog.open) return;
        const bubbles = [...chat.querySelectorAll('.ms-msg-bubble[data-id]')];
        if (!bubbles.length) return;
        const url = new URL(chat.dataset.receiptsUrl, window.location.origin);
        bubbles.forEach(bubble => url.searchParams.append('ids[]', bubble.dataset.id));
        polling = true;
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok) return;
            const data = await response.json();
            const statuses = new Map(data.messages.map(message => [String(message.id), message.is_read]));
            bubbles.forEach(bubble => {
                if (!statuses.has(bubble.dataset.id)) { bubble.closest('.ms-msg-row').remove(); return; }
                const receipt = bubble.querySelector('[data-receipt]');
                if (!receipt) return;
                const read = statuses.get(bubble.dataset.id);
                receipt.classList.toggle('is-read', read);
                receipt.querySelector('i').className = `bi ${read ? 'bi-check2-all' : 'bi-check2'}`;
                receipt.querySelector('span').textContent = read ? 'Read' : 'Sent';
                receipt.title = read ? 'Opened by the recipient' : 'Sent; recipient has not opened it yet';
            });
        } catch (_) { /* Keep the last confirmed receipt until connectivity returns. */ }
        finally { polling = false; }
    }
    setInterval(refreshReceipts, 10000);
    document.addEventListener('visibilitychange', refreshReceipts);
    refreshReceipts();
});
