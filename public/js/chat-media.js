(() => {
    'use strict';
    if (document.documentElement.dataset.chatMediaReady) return;
    document.documentElement.dataset.chatMediaReady = 'true';
    const mainInput = () => document.querySelector('#shoutbox-form textarea[name="content"], .shoutbox-shell textarea[name="content"], .shoutbox-shell textarea');
    let activeInput = mainInput();
    let mediaTarget;
    let mediaKind;
    let playerOpener;
    let previousOverflow;
    const formats = {
        b: ['[b]', '[/b]'], i: ['[i]', '[/i]'], u: ['[u]', '[/u]'], s: ['[s]', '[/s]'],
        quote: ['[quote]', '[/quote]'], code: ['[code]', '[/code]'],
        spoiler: ['[spoiler]', '[/spoiler]'], list: ['[list]\n[*]', '\n[/list]'],
    };
    document.addEventListener('focusin', event => {
        if (event.target.matches('.shoutbox-shell textarea')) activeInput = event.target;
    });
    function editor() {
        return activeInput?.isConnected ? activeInput : mainInput();
    }
    function insert(target, before, after = '', content) {
        if (!target) return false;
        const start = target.selectionStart;
        const end = target.selectionEnd;
        const selected = content ?? target.value.slice(start, end);
        const replacement = before + selected + after;
        if (target.maxLength > 0 && target.value.length - (end - start) + replacement.length > target.maxLength) {
            return false;
        }
        target.setRangeText(replacement, start, end, 'end');
        target.focus({ preventScroll: true });
        if (content === undefined) target.setSelectionRange(start + before.length, start + before.length + selected.length);
        target.dispatchEvent(new Event('input', { bubbles: true }));
        return true;
    }
    function mediaUrl(value) {
        try {
            const url = new URL(value.trim());
            return ['http:', 'https:'].includes(url.protocol) && !url.username && !url.password ? url.href : null;
        } catch (_) { return null; }
    }
    function youtubeId(value) {
        value = value.trim();
        if (/^[A-Za-z0-9_-]{11}$/.test(value)) return value;
        const normalized = mediaUrl(value);
        if (!normalized) return null;
        const url = new URL(normalized);
        const path = url.pathname.replace(/^\/+|\/+$/g, '');
        let id;
        if (['youtu.be', 'www.youtu.be'].includes(url.hostname)) id = path.split('/')[0];
        if (['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'].includes(url.hostname)) {
            if (path === 'watch') id = url.searchParams.get('v');
            else id = /^(?:shorts|embed|live)\/([^/]+)$/.exec(path)?.[1];
        }
        return /^[A-Za-z0-9_-]{11}$/.test(id || '') ? id : null;
    }
    const attachment = document.createElement('dialog');
    attachment.className = 'chat-media-dialog';
    attachment.setAttribute('aria-labelledby', 'chat-media-title');
    attachment.innerHTML = `<form class="chat-media-entry">
        <header><h2 id="chat-media-title">Add media</h2><button type="button" data-media-cancel aria-label="Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <p id="chat-media-description"></p>
        <label for="chat-media-url">URL</label><input id="chat-media-url" type="text" inputmode="url" autocomplete="off" required aria-describedby="chat-media-description chat-media-error">
        <p id="chat-media-error" class="chat-media-error" role="alert"></p>
        <footer><button type="button" data-media-cancel>Cancel</button><button type="submit" class="chat-media-confirm">Add to message</button></footer>
    </form>`;
    document.body.append(attachment);
    const urlInput = attachment.querySelector('input');
    const mediaError = attachment.querySelector('#chat-media-error');
    attachment.querySelectorAll('[data-media-cancel]').forEach(button => button.addEventListener('click', () => attachment.close()));
    attachment.addEventListener('close', () => { mediaTarget?.focus({ preventScroll: true }); });
    attachment.querySelector('form').addEventListener('submit', event => {
        event.preventDefault();
        const value = urlInput.value.trim();
        const id = mediaKind === 'youtube' ? youtubeId(value) : null;
        const normalized = mediaKind === 'youtube' ? (id ? `https://www.youtube.com/watch?v=${id}` : null) : mediaUrl(value);
        if (!normalized) {
            mediaError.textContent = mediaKind === 'youtube' ? 'Enter a valid YouTube video link or video ID.' : 'Enter a valid HTTP or HTTPS URL.';
            urlInput.focus();
            return;
        }
        const tag = mediaKind === 'image' ? 'img' : mediaKind === 'youtube' ? 'youtube' : 'url';
        // Keep the draft intact if the editor disappeared or the message is too long.
        if (!mediaTarget?.isConnected) { attachment.close(); return; }
        if (!insert(mediaTarget, `[${tag}]`, `[/${tag}]`, normalized)) {
            mediaError.textContent = 'This would exceed the message length limit. Shorten your message first.';
            return;
        }
        attachment.close();
    });
    const player = document.createElement('dialog');
    player.className = 'chat-video-dialog';
    player.setAttribute('aria-labelledby', 'chat-video-title');
    player.innerHTML = `<header><div><span>YouTube</span><h2 id="chat-video-title">Chat video player</h2></div><button type="button" data-video-close aria-label="Close video player" autofocus><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <div class="chat-video-stage"></div><footer><a target="_blank" rel="noopener noreferrer">Open on YouTube <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a></footer>`;
    document.body.append(player);
    player.querySelector('[data-video-close]').addEventListener('click', () => player.close());
    player.addEventListener('close', () => {
        // Removing the iframe stops playback and avoids lingering audio.
        player.querySelector('.chat-video-stage').replaceChildren();
        document.documentElement.style.overflow = previousOverflow;
        playerOpener?.isConnected && playerOpener.focus({ preventScroll: true });
    });
    function closeOnBackdrop(dialog) {
        dialog.addEventListener('click', event => {
            if (event.target !== dialog) return;
            const rect = dialog.getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
        });
    }
    closeOnBackdrop(attachment);
    closeOnBackdrop(player);
    document.addEventListener('click', event => {
        const video = event.target.closest('[data-youtube-id]');
        if (video) {
            const id = video.dataset.youtubeId;
            if (!/^[A-Za-z0-9_-]{11}$/.test(id)) return;
            event.preventDefault();
            playerOpener = video;
            const frame = document.createElement('iframe');
            frame.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0`;
            frame.title = 'YouTube video player';
            frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
            frame.allowFullscreen = true;
            frame.referrerPolicy = 'strict-origin-when-cross-origin';
            player.querySelector('.chat-video-stage').replaceChildren(frame);
            player.querySelector('a').href = `https://www.youtube.com/watch?v=${id}`;
            previousOverflow = document.documentElement.style.overflow;
            document.documentElement.style.overflow = 'hidden';
            player.showModal();
            return;
        }
        const format = event.target.closest('[data-chat-format]');
        if (format) {
            const pair = formats[format.dataset.chatFormat];
            if (pair && !insert(format.closest('form')?.querySelector('textarea') || editor(), ...pair)) {
                const feedback = document.getElementById('chat-feedback');
                if (feedback) feedback.textContent = 'Shorten your message to add more formatting.';
            }
            return;
        }
        const button = event.target.closest('[data-chat-media]');
        if (!button) return;
        mediaTarget = button.closest('form')?.querySelector('textarea') || editor();
        if (!mediaTarget) return;
        mediaKind = button.dataset.chatMedia;
        const selected = mediaTarget.value.slice(mediaTarget.selectionStart, mediaTarget.selectionEnd);
        attachment.querySelector('h2').textContent = mediaKind === 'image' ? 'Add an image' : mediaKind === 'youtube' ? 'Add a YouTube video' : 'Add a link';
        attachment.querySelector('#chat-media-description').textContent = mediaKind === 'image' ? 'Paste a direct image URL. Images fit the chat bubble and open larger when clicked.' : mediaKind === 'youtube' ? 'Paste a YouTube link. Members can click the preview to open and play the video.' : 'Paste the link you want to share.';
        urlInput.value = selected;
        urlInput.placeholder = mediaKind === 'youtube' ? 'https://www.youtube.com/watch?v=…' : 'https://…';
        mediaError.textContent = '';
        attachment.showModal();
        urlInput.focus();
    });
})();
