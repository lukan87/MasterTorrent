@if($latestUsers->count())

    @php $user = $latestUsers->first(); @endphp

    <div id="lu-popup" class="modern-lu-popup">
        <div class="lu-glow"></div>

        <div class="lu-main-content">
            <div class="lu-avatar-wrap">
                <div class="lu-avatar-ring"></div>
                <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.$user->name }}"
                     class="lu-avatar-img"
                     alt="{{ $user->name }}">
                <span class="lu-online-dot"></span>
            </div>

            <div class="lu-user-info">
                <div class="lu-top-line">
                    <span class="lu-badge">
                        <i class="bi bi-stars"></i>
                        NEW MEMBER
                    </span>
                </div>

                <div class="lu-username">
                    <a href="{{ route('profile.show', $user->id) }}" class="lu-user-link">
                        {{ $user->name }}
                    </a>
                </div>

                <div class="lu-subtext">
                    <i class="bi bi-clock-history me-1"></i>
                    joined • {{ $user->registered_ago }}
                </div>
            </div>

            <div class="lu-side-icon">
                <i class="bi bi-person-plus-fill"></i>
            </div>
        </div>

        <div class="lu-progress-wrap">
            <div class="lu-progress-bar"></div>
        </div>
    </div>

<style>
.modern-lu-popup {
    position: fixed;
    left: 22px;
    bottom: 22px;
    width: 340px;
    max-width: calc(100vw - 24px);
    overflow: hidden;
    border-radius: .75rem;
    background: linear-gradient(135deg, rgba(22,32,51,.97), rgba(15,23,42,.95));
    border: 1px solid var(--ui-border, rgba(255,255,255,.09));
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    box-shadow: 0 18px 45px rgba(0,0,0,.48), inset 0 1px 0 rgba(255,255,255,.035);
    z-index: 999999;
    animation: luPopupIn .45s cubic-bezier(.22,1,.36,1);
}

.lu-glow {
    position: absolute;
    top: -85px;
    right: -75px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34,211,201,.16), transparent 70%);
    pointer-events: none;
}

.lu-main-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
}

.lu-avatar-wrap {
    position: relative;
    flex-shrink: 0;
    width: 56px;
    height: 56px;
}

.lu-avatar-ring {
    position: absolute;
    inset: -2px;
    border-radius: 50%;
    background: linear-gradient(135deg, #22d3c5, #14b8a6);
}

.lu-avatar-img {
    position: relative;
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid rgba(15,23,42,.98);
    z-index: 2;
}

.lu-online-dot {
    position: absolute;
    right: 1px;
    bottom: 1px;
    width: 13px;
    height: 13px;
    border-radius: 50%;
    background: #22c55e;
    border: 2px solid #0f172a;
    z-index: 3;
    box-shadow: 0 0 8px rgba(34,197,94,.45);
}

.lu-user-info {
    flex: 1;
    min-width: 0;
}

.lu-top-line {
    margin-bottom: 5px;
}

.lu-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: .45rem;
    font-size: .64rem;
    font-weight: 800;
    letter-spacing: .55px;
    color: #8be7df;
    background: rgba(34,211,201,.09);
    border: 1px solid rgba(34,211,201,.18);
}

.lu-badge i {
    font-size: .7rem;
}

.lu-username {
    font-size: .98rem;
    font-weight: 700;
    line-height: 1.25;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.lu-user-link {
    color: #f1f5f9;
    text-decoration: none;
    transition: color .2s ease;
}

.lu-user-link:hover {
    color: #67e8df;
}

.lu-subtext {
    color: rgba(226,232,240,.58);
    font-size: .76rem;
    line-height: 1.35;
}

.lu-subtext i {
    color: #5eead4;
}

.lu-side-icon {
    width: 40px;
    height: 40px;
    border-radius: .6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(34,211,201,.07);
    border: 1px solid rgba(34,211,201,.12);
    color: #5eead4;
    font-size: 1rem;
}

.lu-progress-wrap {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 3px;
    background: rgba(255,255,255,.035);
}

.lu-progress-bar {
    height: 100%;
    width: 100%;
    background: linear-gradient(90deg, #14b8a6, #22d3c5);
    animation: luProgress 5s linear forwards;
}

@media (max-width: 768px) {
    .modern-lu-popup {
        left: 12px;
        right: 12px;
        bottom: 14px;
        width: auto;
        max-width: none;
        border-radius: .7rem;
    }

    .lu-main-content {
        padding: 14px;
        gap: 12px;
    }

    .lu-avatar-wrap {
        width: 50px;
        height: 50px;
    }

    .lu-side-icon {
        display: none;
    }

    .lu-username {
        font-size: .94rem;
    }

    .lu-subtext {
        font-size: .73rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .modern-lu-popup,
    .lu-progress-bar {
        animation: none;
    }
}

@keyframes luPopupIn {
    from {
        opacity: 0;
        transform: translateY(18px) scale(.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes luProgress {
    from { width: 100%; }
    to { width: 0%; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const popup = document.getElementById('lu-popup');
    if (!popup) return;

    const progress = popup.querySelector('.lu-progress-bar');
    let timeout = setTimeout(closePopup, 5000);

    function closePopup() {
        popup.style.transition = 'opacity .35s ease, transform .35s ease';
        popup.style.opacity = '0';
        popup.style.transform = 'translateY(12px) scale(.97)';
        setTimeout(() => popup.remove(), 350);
    }

    popup.addEventListener('mouseenter', () => {
        clearTimeout(timeout);
        if (progress) progress.style.animationPlayState = 'paused';
    });

    popup.addEventListener('mouseleave', () => {
        timeout = setTimeout(closePopup, 2000);
        if (progress) progress.style.animationPlayState = 'running';
    });
});
</script>

@endif
