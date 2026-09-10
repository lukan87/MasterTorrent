<div class="container mt-3">

    <div class="card online-users-card">

        <div class="card-header online-users-header border-0">

            <div class="online-icon">
                <i class="fas fa-users"></i>
            </div>

            <div class="online-header-content">
                <div class="online-title">
                    Users Online
                </div>

                <div class="online-count">
                    {{ count($onlineUsers) }} users online
                    <span class="live-dot"></span>
                </div>
            </div>

        </div>

        <div class="card-body online-users-body">

            @if($onlineUsers->isEmpty())

                <div class="empty-online-users">
                    <i class="fas fa-user-slash"></i>
                    <span>No users are currently online.</span>
                </div>

            @else

                <div id="online-users-list" class="online-users-list">

                    @foreach($onlineUsers->take(100) as $user)

                        <div
                            class="online-user {{ $loop->index >= 100 ? 'extra-user' : '' }}"
                        >

                            <a
    href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}"
    class="online-user-link"
    style="--user-color: {{ \App\Models\UserClass::getClassColor($user->user_class) }}"
    data-bs-toggle="tooltip"
    data-bs-placement="top"
    data-bs-html="true"
    title="
        <strong>{{ \App\Models\UserClass::getClassName($user->user_class) }}</strong><br>
        Uploaded: {{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}<br>
        Downloaded: {{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}
    "
>

                                <span class="online-user-dot"></span>

                                <span class="online-user-name">
                                    {{ $user->name }}
                                </span>

                                @if($user->warned)
                                    <i
                                        class="fas fa-exclamation-triangle online-warn-icon"
                                        title="Warned user"
                                    ></i>
                                @endif

                                @if($user->donor)
                                    <i 
                                        class="fas fa-star online-donor-icon"
                                        title="Donor"
                                    ></i>
                                @endif

                            </a>

                            
                        </div>

                    @endforeach

                </div>

                @if($onlineUsers->count() > 100)

                    <div class="text-center mt-3">

                        <button
                            type="button"
                            id="toggle-users-btn"
                            class="online-show-more"
                        >
                            <span>Show more</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>

                    </div>

                @endif

            @endif

        </div>

    </div>

</div>


<style>

.online-users-card {
    position: relative;
    overflow: visible;
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );
    border: 1px solid var(--ui-border);
    border-radius: .9rem;
    box-shadow: 0 10px 28px rgba(0, 0, 0, .24);
}

.online-users-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(
        180deg,
        var(--ui-accent),
        var(--ui-accent-strong)
    );
    opacity: .9;
}

.online-users-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .85rem 1rem;
    background: transparent;
    border-bottom: 1px solid var(--ui-border) !important;
}

.online-icon {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: .65rem;
    color: var(--ui-accent);
    background: rgba(45, 212, 191, .08);
    border: 1px solid rgba(45, 212, 191, .18);
    font-size: 14px;
}

.online-header-content {
    min-width: 0;
}

.online-title {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.25;
}

.online-count {
    display: flex;
    align-items: center;
    gap: .4rem;
    margin-top: .15rem;
    color: rgba(255, 255, 255, .58);
    font-size: 13px;
}

.live-dot {
    width: 7px;
    height: 7px;
    display: inline-block;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 8px rgba(34, 197, 94, .5);
}

.online-users-body {
    padding: 1rem;
    background: transparent;
}

.online-users-list {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem .65rem;
}

.online-user {
    position: relative;
}

.online-user.extra-user {
    display: none;
}

.online-user-link {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .35rem .55rem;
    color: var(--user-color);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: .55rem;
    background: rgba(255, 255, 255, .025);
    transition:
        background .18s ease,
        border-color .18s ease,
        transform .18s ease;
}

.online-user-link:hover {
    color: var(--user-color);
    background: rgba(45, 212, 191, .07);
    border-color: rgba(45, 212, 191, .2);
    transform: translateY(-1px);
}

.online-user-dot {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 7px rgba(34, 197, 94, .45);
}

.online-user-name {
    color: inherit;
}

.online-warn-icon {
    color: #f59e0b;
    font-size: 11px;
}

.tooltip .tooltip-inner {
    max-width: 280px;
    padding: .6rem .75rem;
    text-align: left;
    font-size: 13px;
    line-height: 1.5;
    color: rgba(255, 255, 255, .8);
    background: rgba(15, 23, 42, .98);
    border: 1px solid var(--ui-border);
    border-radius: .6rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, .35);
}

.tooltip.show {
    opacity: 1;
}

.tooltip strong {
    color: var(--ui-accent);
    font-size: 14px;
}

.online-donor-icon {
    color: #facc15;
    font-size: 10px;
}

.empty-online-users {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .55rem;
    min-height: 70px;
    color: rgba(255, 255, 255, .55);
    font-size: 14px;
}

.empty-online-users i {
    color: var(--ui-accent);
    font-size: 15px;
}

.online-show-more {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .4rem .7rem;
    color: var(--ui-accent);
    background: rgba(45, 212, 191, .05);
    border: 1px solid var(--ui-border);
    border-radius: .55rem;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition:
        background .18s ease,
        border-color .18s ease,
        transform .18s ease;
}

.online-show-more:hover {
    color: var(--ui-accent-strong);
    background: rgba(45, 212, 191, .09);
    border-color: rgba(45, 212, 191, .25);
    transform: translateY(-1px);
}

.online-show-more i {
    font-size: 10px;
    transition: transform .18s ease;
}

.online-users-legend {
    padding: .75rem 1rem;
    border-top: 1px solid var(--ui-border);
    background: rgba(0, 0, 0, .08);
}

.legend-title {
    margin-bottom: .5rem;
    color: rgba(255, 255, 255, .55);
    font-size: 12px;
    font-weight: 600;
}

.legend-list {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem .75rem;
}

.legend-item {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    color: var(--legend-color);
    font-size: 13px;
    font-weight: 600;
}

.legend-dot {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--legend-color);
    box-shadow: 0 0 5px color-mix(
        in srgb,
        var(--legend-color) 40%,
        transparent
    );
}

.legend-name {
    color: inherit;
}

@media (max-width: 576px) {

    .online-users-header {
        padding: .75rem;
    }

    .online-users-body {
        padding: .75rem;
    }

    .online-user-link {
        font-size: 14px;
    }

    .online-users-legend {
        padding: .7rem .75rem;
    }

    .legend-list {
        gap: .35rem .65rem;
    }

    .legend-item {
        font-size: 13px;
    }

    .online-user-tooltip {
        display: none;
    }

}

</style>


<script>

document.addEventListener("DOMContentLoaded", function () {

    const list = document.getElementById("online-users-list");
    const button = document.getElementById("toggle-users-btn");

    if (!list || !button) {
        return;
    }

    const extraUsers = list.querySelectorAll(".extra-user");
    const buttonText = button.querySelector("span");
    const icon = button.querySelector("i");

    button.addEventListener("click", function () {

        const isExpanded = button.classList.toggle("expanded");

        extraUsers.forEach(function (user) {
            user.style.display = isExpanded ? "block" : "none";
        });

        if (isExpanded) {
            buttonText.textContent = "Show less";
            icon.classList.remove("fa-chevron-down");
            icon.classList.add("fa-chevron-up");
        } else {
            buttonText.textContent = "Show more";
            icon.classList.remove("fa-chevron-up");
            icon.classList.add("fa-chevron-down");
        }

    });

});

</script>