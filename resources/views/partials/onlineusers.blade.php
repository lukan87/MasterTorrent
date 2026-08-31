<div class="container mt-3">

    <div class="card online-users-card">

        {{-- Header --}}
        <div class="card-header online-users-header border-0">

            <div class="d-flex align-items-center justify-content-between">

                <div class="d-flex align-items-center gap-2">

                    <div class="online-icon">

                        <i class="bi bi-people-fill"></i>

                    </div>

                    <div class="d-flex align-items-center">

                        <span class="online-title">

                            Online Users

                        </span>

                        <span class="online-count ms-2">

                            {{ $onlineUserCount }}

                        </span>

                    </div>

                </div>

                <span class="live-dot" title="Live"></span>

            </div>

        </div>

        {{-- Body --}}
        <div class="card-body online-users-body">

            @if ($onlineUsers->isEmpty())

                <div class="empty-online-users">

                    <i class="bi bi-wifi-off me-2"></i>

                    No users are currently online.

                </div>

            @else

                {{-- Users --}}
                <div id="online-users-list" class="online-users-list">

                    @foreach ($onlineUsers as $index => $user)

                        <span class="{{ $index >= 100 ? 'd-none extra-user' : '' }}">

                            <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}"
                               class="online-user-link"
                               style="--user-color: {{ \App\Models\UserClass::getClassColor($user->user_class) }}"
                               data-bs-toggle="tooltip"
                               data-bs-html="true"
                               data-bs-title='
                                   <div class="text-center">
                                       <strong>{{ \App\Models\UserClass::getClassName($user->user_class) }}</strong>
                                       <hr class="my-1">
                                       <small>⬆ {{ App\Helpers\FormatHelper::formatSize($user->uploaded) }}</small><br>
                                       <small>⬇ {{ App\Helpers\FormatHelper::formatSize($user->downloaded) }}</small>
                                   </div>'>

                                {{ $user->name }}

                                @if($user->warned)

                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>

                                @endif

                                @if($user->donor === 'yes')

                                    <i class="bi bi-star-fill text-warning ms-1"></i>

                                @endif

                            </a>

                            @if(!$loop->last)

                                <span class="comma">•</span>

                            @endif

                        </span>

                    @endforeach

                </div>

                {{-- Show More --}}
                @if($onlineUsers->count() > 100)

                    <div class="mt-3">

                        <button id="toggle-users-btn"
                                class="btn btn-sm btn-outline-info rounded-pill px-3 py-1">

                            <i class="bi bi-chevron-down me-1"></i>

                            <span class="btn-text">

                                Show more

                            </span>

                        </button>

                    </div>

                @endif

                {{-- Legend --}}
                <div class="legend-section">

                    <div class="legend-wrapper">

                        @php
                            $orderedClasses = [
                                \App\Models\UserClass::OWNER,
                                \App\Models\UserClass::ADMIN,
                                \App\Models\UserClass::MODERATOR,
                                \App\Models\UserClass::UPLOADER,
                                \App\Models\UserClass::SUPERUSER,
                                \App\Models\UserClass::VIP,
                                \App\Models\UserClass::ELITE_USER,
                                \App\Models\UserClass::USER,
                            ];
                        @endphp

                        @foreach ($orderedClasses as $class)

                            <span class="legend-item"
                                  style="--legend-color: {{ \App\Models\UserClass::getClassColor($class) }}">

                                ● {{ \App\Models\UserClass::getClassName($class) }}

                            </span>

                        @endforeach

                    </div>

                </div>

            @endif

        </div>

    </div>

</div>

<style>

/* =========================================
   CARD
========================================= */

.online-users-card{

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.04),
            rgba(255,255,255,.02)
        );

    backdrop-filter:blur(12px);

    border:
        1px solid rgba(255,255,255,.06);

    border-radius:22px;

    overflow:hidden;

    box-shadow:
        0 10px 30px rgba(0,0,0,.22);
}

/* =========================================
   HEADER
========================================= */

.online-users-header{

    padding:1rem 1.25rem .75rem;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.online-icon{

    width:34px;
    height:34px;

    border-radius:12px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #22c55e,
            #16a34a
        );

    color:white;

    font-size:.95rem;

    box-shadow:
        0 6px 18px rgba(34,197,94,.28);
}

.online-title{

    color:#fff;

    font-size:1rem;

    font-weight:700;
}

.online-count{

    padding:3px 10px;

    border-radius:999px;

    background:
        rgba(255,255,255,.06);

    color:#cbd5e1;

    font-size:.78rem;

    font-weight:600;
}

/* =========================================
   LIVE DOT
========================================= */

.live-dot{

    width:10px;
    height:10px;

    background:#22c55e;

    border-radius:50%;

    animation:pulse 2s infinite;
}

@keyframes pulse {

    0%{

        box-shadow:
            0 0 0 0 rgba(34,197,94,.6);
    }

    70%{

        box-shadow:
            0 0 0 8px rgba(34,197,94,0);
    }

    100%{

        box-shadow:
            0 0 0 0 rgba(34,197,94,0);
    }
}

/* =========================================
   BODY
========================================= */

.online-users-body{

    padding:1rem 1.25rem 1.2rem;
}

.online-users-list{

    line-height:2;

    font-size:.95rem;
}

/* =========================================
   USER LINKS
========================================= */

.online-user-link{

    color:var(--user-color);

    text-decoration:none;

    font-size:.95rem;

    font-weight:600;

    position:relative;

    transition:.2s ease;
}

.online-user-link:hover{

    opacity:.95;

    color:var(--user-color);
}

.online-user-link::after{

    content:'';

    position:absolute;

    left:0;
    bottom:-2px;

    width:0%;

    height:1px;

    background:var(--user-color);

    transition:width .2s ease;
}

.online-user-link:hover::after{

    width:100%;
}

.comma{

    color:
        rgba(255,255,255,.25);

    margin:
        0 .35rem;
}

/* =========================================
   EMPTY
========================================= */

.empty-online-users{

    padding:1rem;

    border-radius:14px;

    background:
        rgba(255,255,255,.03);

    color:#9ca3af;

    font-size:.92rem;
}

/* =========================================
   LEGEND
========================================= */

.legend-section{

    margin-top:1rem;

    padding-top:1rem;

    border-top:
        1px solid rgba(255,255,255,.05);
}

.legend-wrapper{

    display:flex;

    flex-wrap:wrap;

    gap:.7rem 1rem;
}

.legend-item{

    color:var(--legend-color);

    font-size:.8rem;

    font-weight:600;

    opacity:.9;
}

/* =========================================
   BUTTON
========================================= */

#toggle-users-btn{

    font-size:.82rem;

    border-color:
        rgba(59,130,246,.25);

    color:#93c5fd;
}

#toggle-users-btn:hover{

    background:
        rgba(59,130,246,.15);

    color:#fff;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .online-users-header{

        padding:.9rem 1rem .7rem;
    }

    .online-users-body{

        padding:1rem;
    }

    .online-users-list{

        line-height:1.9;

        font-size:.9rem;
    }

    .online-user-link{

        font-size:.9rem;
    }

    .legend-wrapper{

        gap:.55rem .8rem;
    }

    .legend-item{

        font-size:.75rem;
    }

}

</style>

<script>

document.getElementById('toggle-users-btn')?.addEventListener('click', function () {

    const extraUsers = document.querySelectorAll('.extra-user');

    const btnText = this.querySelector('.btn-text');

    const icon = this.querySelector('i');

    const isHidden = extraUsers[0]?.classList.contains('d-none');

    extraUsers.forEach(el => el.classList.toggle('d-none'));

    if (isHidden) {

        btnText.textContent = "Show less";

        icon.classList.remove('bi-chevron-down');

        icon.classList.add('bi-chevron-up');

    } else {

        btnText.textContent = "Show more";

        icon.classList.remove('bi-chevron-up');

        icon.classList.add('bi-chevron-down');

    }

});

</script>