@php
    $discussionComments = $discussionComments ?? \App\Models\Comment::where('commentable_type', $commentType)->where('commentable_id', $commentTarget->id)->discussion()->paginate(10, ['*'], 'comments_page')->withQueryString()->fragment('discussion');
@endphp
<section class="discussion mt-4" id="discussion" aria-labelledby="discussion-title">
    <header class="discussion-heading">
        <h3 id="discussion-title">Discussion for {{ $commentTarget->name ?? $commentTarget->title ?? 'this title' }} <span>{{ $discussionComments->total() }}</span></h3>
    </header>
    @if($errors->any())
        <div class="discussion-notice" role="alert">{{ $errors->first() }}</div>
    @endif
    @auth
        @if(auth()->user()->commentblock)
            <div class="discussion-notice">Your account is currently restricted from posting comments and reactions.</div>
        @else
            @include('comments.form', ['formId' => 'new-comment', 'parentId' => null])
        @endif
    @else
        <p class="discussion-notice">Sign in to join the discussion.</p>
    @endauth
    <div class="discussion-list">
        @forelse($discussionComments as $comment)
            @include('comments.card', ['isReply' => false])
        @empty
            <div class="discussion-empty"><i class="bi bi-chat-heart" aria-hidden="true"></i><h4>Start the conversation</h4><p>No comments yet. What did you think?</p></div>
        @endforelse
    </div>
    @if($discussionComments->hasPages())
        <nav class="discussion-pagination" aria-label="Comment pages">{{ $discussionComments->links('pagination::bootstrap-5') }}</nav>
    @endif
</section>
@once
@include('comments.styles')
<script>
document.querySelectorAll('.discussion-reaction-picker').forEach(function (picker) {
    const toggle = picker.querySelector('.discussion-reaction-toggle');
    const options = picker.querySelector('.discussion-reaction-options');
    const setOpen = function (open) {
        options.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    };
    picker.addEventListener('pointerenter', function (event) {
        if (event.pointerType === 'mouse') setOpen(true);
    });
    picker.addEventListener('pointerleave', function (event) {
        if (event.pointerType === 'mouse' && !picker.contains(document.activeElement)) setOpen(false);
    });
    toggle.addEventListener('click', function () { setOpen(options.hidden); });
    picker.addEventListener('focusout', function (event) {
        if (!picker.contains(event.relatedTarget)) setOpen(false);
    });
    picker.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') { setOpen(false); toggle.focus(); }
    });
    document.addEventListener('click', function (event) {
        if (!picker.contains(event.target)) setOpen(false);
    });
});

document.addEventListener('submit', async function (event) {
    const form = event.target;
    if (!form.matches('.discussion-reaction-picker form')) return;
    event.preventDefault();
    const picker = form.closest('.discussion-reaction-picker');
    if (picker.dataset.busy) return;
    const area = picker.closest('.discussion-reaction-area');
    const status = area.querySelector('.discussion-reaction-status');
    const payload = new FormData(form);
    picker.dataset.busy = 'true';
    picker.querySelectorAll('button').forEach(button => button.disabled = true);
    status.textContent = '';
    status.classList.add('visually-hidden');
    try {
        const response = await fetch(form.action, {
            method: 'POST', body: payload, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error('Could not save your reaction. Please try again.');
        const data = await response.json();
        const tooltipText = type => type.charAt(0).toUpperCase() + type.slice(1) + ': ' + ((data.reactors[type] || []).join(', ') || 'No reactions yet');
        const updateTooltip = (element, title) => {
            window.bootstrap?.Tooltip.getInstance(element)?.dispose();
            element.setAttribute('data-bs-toggle', 'tooltip');
            element.setAttribute('data-bs-title', title);
            if (window.bootstrap?.Tooltip) new bootstrap.Tooltip(element, { html: false });
        };
        const types = @json(\App\Models\CommentReaction::TYPES);
        const selected = data.selected || 'like';
        const main = picker.querySelector('.discussion-like');
        main.form.querySelector('[name="reaction"]').value = selected;
        main.replaceChildren();
        const emoji = document.createElement('span');
        emoji.setAttribute('aria-hidden', 'true');
        emoji.textContent = types[selected];
        main.append(emoji, ' ' + selected.charAt(0).toUpperCase() + selected.slice(1));
        main.setAttribute('aria-pressed', String(Boolean(data.selected)));
        main.setAttribute('aria-label', data.selected ? 'Remove your reaction' : 'Like this comment');
        picker.querySelectorAll('.discussion-reaction-options form').forEach(option => {
            updateTooltip(option.querySelector('button'), tooltipText(option.querySelector('[name="reaction"]').value));
            option.querySelector('button').setAttribute('aria-pressed', String(option.querySelector('[name="reaction"]').value === data.selected));
        });
        const totals = area.querySelector('.discussion-reaction-totals');
        totals.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => window.bootstrap?.Tooltip.getInstance(element)?.dispose());
        totals.replaceChildren();
        let total = 0;
        Object.entries(types).forEach(([type, icon]) => {
            const count = Number(data.counts[type] || 0);
            total += count;
            if (!count) return;
            const item = document.createElement('span');
            item.textContent = icon + ' ' + count;
            item.tabIndex = 0;
            totals.append(item);
            updateTooltip(item, tooltipText(type));
        });
        totals.setAttribute('aria-label', total + ' reactions');
        picker.querySelector('.discussion-reaction-options').hidden = true;
        picker.querySelector('.discussion-reaction-toggle').setAttribute('aria-expanded', 'false');
        status.textContent = 'Reaction updated.';
    } catch (error) {
        status.classList.remove('visually-hidden');
        status.textContent = error.message;
    } finally {
        delete picker.dataset.busy;
        picker.querySelectorAll('button').forEach(button => button.disabled = false);
    }
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-comment-tag]');
    if (!button) return;
    const textarea = button.closest('form').querySelector('textarea');
    const tag = button.dataset.commentTag;
    const start = textarea.selectionStart, end = textarea.selectionEnd;
    const selected = textarea.value.slice(start, end);
    textarea.setRangeText('[' + tag + ']' + selected + '[/' + tag + ']', start, end, 'select');
    textarea.focus();
});
</script>
@endonce
