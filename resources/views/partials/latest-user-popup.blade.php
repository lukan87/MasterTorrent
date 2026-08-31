@if($latestUsers->count())

    @php $user = $latestUsers->first(); @endphp

    <div id="lu-popup" class="modern-lu-popup">

        {{-- Glow --}}
        <div class="lu-glow"></div>

        {{-- Content --}}
        <div class="lu-main-content">

            {{-- Avatar --}}
            <div class="lu-avatar-wrap">

                <div class="lu-avatar-ring"></div>

                <img
                    src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.$user->name }}"
                    class="lu-avatar-img"
                    alt="{{ $user->name }}"
                >

                <span class="lu-online-dot"></span>

            </div>

            {{-- Text --}}
            <div class="lu-user-info">

                <div class="lu-top-line">

                    <span class="lu-badge">

                        <i class="bi bi-stars"></i>

                        NEW MEMBER

                    </span>

                </div>

                <div class="lu-username">

                    <a href="{{ route('profile.show', $user->id) }}"
                       class="lu-user-link">

                        {{ $user->name }}

                    </a>

                </div>

                <div class="lu-subtext">

                    <i class="bi bi-clock-history me-1"></i>

                    joined • {{ $user->registered_ago }}

                </div>

            </div>

            {{-- Icon --}}
            <div class="lu-side-icon">

                <i class="bi bi-person-plus-fill"></i>

            </div>

        </div>

        {{-- Progress --}}
        <div class="lu-progress-wrap">

            <div class="lu-progress-bar"></div>

        </div>

    </div>

<style>

/* =========================================
   POPUP
========================================= */

.modern-lu-popup{

    position:fixed;

    left:22px;
    bottom:22px;

    width:340px;
    max-width:calc(100vw - 24px);

    overflow:hidden;

    border-radius:24px;

    background:
        linear-gradient(
            145deg,
            rgba(18,22,35,.92),
            rgba(10,14,24,.95)
        );

    border:
        1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(18px);

    box-shadow:
        0 25px 60px rgba(0,0,0,.55),
        inset 0 1px 0 rgba(255,255,255,.04);

    z-index:999999;

    animation:luPopupIn .55s cubic-bezier(.22,1,.36,1);
}

/* =========================================
   GLOW
========================================= */

.lu-glow{

    position:absolute;

    top:-80px;
    right:-80px;

    width:180px;
    height:180px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.22),
            transparent 70%
        );

    pointer-events:none;
}

/* =========================================
   CONTENT
========================================= */

.lu-main-content{

    position:relative;

    z-index:2;

    display:flex;

    align-items:center;

    gap:16px;

    padding:18px;
}

/* =========================================
   AVATAR
========================================= */

.lu-avatar-wrap{

    position:relative;

    flex-shrink:0;

    width:58px;
    height:58px;
}

.lu-avatar-ring{

    position:absolute;

    inset:-3px;

    border-radius:50%;

    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #8b5cf6,
            #06b6d4
        );

    animation:rotateRing 6s linear infinite;
}

.lu-avatar-img{

    position:relative;

    width:100%;
    height:100%;

    object-fit:cover;

    border-radius:50%;

    border:3px solid rgba(15,20,35,.95);

    z-index:2;
}

.lu-online-dot{

    position:absolute;

    right:2px;
    bottom:2px;

    width:14px;
    height:14px;

    border-radius:50%;

    background:#22c55e;

    border:2px solid #111827;

    z-index:3;

    box-shadow:
        0 0 12px rgba(34,197,94,.8);

    animation:luPulse 2s infinite;
}

/* =========================================
   TEXT
========================================= */

.lu-user-info{

    flex:1;

    min-width:0;
}

.lu-top-line{

    margin-bottom:6px;
}

.lu-badge{

    display:inline-flex;

    align-items:center;

    gap:5px;

    padding:5px 10px;

    border-radius:999px;

    font-size:.66rem;

    font-weight:800;

    letter-spacing:.8px;

    color:#93c5fd;

    background:
        rgba(59,130,246,.12);

    border:
        1px solid rgba(59,130,246,.18);
}

.lu-username{

    font-size:1rem;

    font-weight:800;

    line-height:1.2;

    margin-bottom:4px;

    white-space:nowrap;

    overflow:hidden;

    text-overflow:ellipsis;
}

.lu-user-link{

    color:white;

    text-decoration:none;

    transition:.25s ease;
}

.lu-user-link:hover{

    color:#7dd3fc;

    text-shadow:
        0 0 14px rgba(125,211,252,.5);
}

.lu-subtext{

    color:rgba(255,255,255,.58);

    font-size:.8rem;
}

/* =========================================
   SIDE ICON
========================================= */

.lu-side-icon{

    width:44px;
    height:44px;

    border-radius:16px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid rgba(255,255,255,.06);

    color:#60a5fa;

    font-size:1.1rem;

    flex-shrink:0;
}

/* =========================================
   PROGRESS
========================================= */

.lu-progress-wrap{

    position:absolute;

    left:0;
    right:0;
    bottom:0;

    height:4px;

    background:
        rgba(255,255,255,.04);
}

.lu-progress-bar{

    height:100%;

    width:100%;

    background:
        linear-gradient(
            90deg,
            #3b82f6,
            #8b5cf6,
            #06b6d4
        );

    animation:luProgress 5s linear forwards;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-lu-popup{

        left:12px;
        right:12px;
        bottom:14px;

        width:auto;

        border-radius:20px;
    }

    .lu-main-content{

        padding:15px;
        gap:14px;
    }

    .lu-avatar-wrap{

        width:52px;
        height:52px;
    }

    .lu-side-icon{

        display:none;
    }
}

/* =========================================
   ANIMATIONS
========================================= */

@keyframes luPopupIn{

    from{

        opacity:0;

        transform:
            translateY(25px)
            scale(.92);
    }

    to{

        opacity:1;

        transform:
            translateY(0)
            scale(1);
    }
}

@keyframes luProgress{

    from{
        width:100%;
    }

    to{
        width:0%;
    }
}

@keyframes luPulse{

    0%{
        box-shadow:
            0 0 0 0 rgba(34,197,94,.6);
    }

    70%{
        box-shadow:
            0 0 0 10px rgba(34,197,94,0);
    }

    100%{
        box-shadow:
            0 0 0 0 rgba(34,197,94,0);
    }
}

@keyframes rotateRing{

    from{
        transform:rotate(0deg);
    }

    to{
        transform:rotate(360deg);
    }
}

</style>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const popup = document.getElementById('lu-popup');

    if (!popup) return;

    const progress = popup.querySelector('.lu-progress-bar');

    let timeout = setTimeout(closePopup, 5000);

    function closePopup() {

        popup.style.transition = 'all .45s cubic-bezier(.22,1,.36,1)';

        popup.style.opacity = '0';

        popup.style.transform = 'translateY(15px) scale(.94)';

        setTimeout(() => popup.remove(), 450);
    }

    popup.addEventListener('mouseenter', () => {

        clearTimeout(timeout);

        progress.style.animationPlayState = 'paused';
    });

    popup.addEventListener('mouseleave', () => {

        timeout = setTimeout(closePopup, 2000);

        progress.style.animationPlayState = 'running';
    });

});

</script>

@endif