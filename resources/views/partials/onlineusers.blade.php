<details class="chat-presence mb-3" id="chat-presence" open>
    <summary class="chat-presence-summary">
        <span class="chat-presence-status" aria-hidden="true"></span>
        <span class="chat-presence-heading">Online now <span class="chat-presence-count">{{ $onlineUsers->count() }}</span></span>
        <span class="chat-presence-preview" aria-hidden="true">
            @foreach($onlineUsers->take(5) as $user)
                <span style="--member-color: {{ \App\Models\UserClass::getClassColor($user->user_class) }}">{{ mb_substr($user->name, 0, 1) }}</span>
            @endforeach
            @if($onlineUsers->count() > 5)
                <span class="chat-presence-overflow">+{{ $onlineUsers->count() - 5 }}</span>
            @endif
        </span>
        <i class="bi bi-chevron-down chat-presence-chevron" aria-hidden="true"></i>
    </summary>

    <div class="chat-presence-body">
        @if($onlineUsers->isEmpty())
            <p class="chat-presence-empty mb-0">No members are online right now.</p>
        @else
            <div class="chat-presence-toolbar">
                <span>Find a familiar face. Join the conversation.</span>
                <label class="chat-presence-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="online-member-search" placeholder="Find a member…" aria-label="Search online members" aria-controls="online-users-list" autocomplete="off">
                </label>
            </div>
            <ul id="online-users-list" class="chat-presence-members" aria-label="Online members">
                @foreach($onlineUsers as $user)
                    <li data-member-name="{{ $user->name }}">
                        <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}"
                           class="chat-member" style="--member-color: {{ \App\Models\UserClass::getClassColor($user->user_class) }}"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           data-bs-container="body"
                           data-bs-html="true"
                           data-bs-custom-class="chat-member-tooltip"
                           title="{{ '<strong>Class:</strong> ' . e(\App\Models\UserClass::getClassName($user->user_class)) . (filled($user->title) ? '<br><strong>Title:</strong> ' . e($user->title) : '') . '<br><strong>Uploaded:</strong> ' . e(\App\Helpers\FormatHelper::formatSize($user->uploaded)) . '<br><strong>Downloaded:</strong> ' . e(\App\Helpers\FormatHelper::formatSize($user->downloaded)) }}">
                            <span class="chat-member-initial" aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>
                            <span class="chat-member-name">{{ $user->name }}</span>
                            @if(auth()->id() === $user->id)
                                <span class="chat-member-you">you</span>
                            @endif
                            @if($user->warned)
                                <i class="bi bi-exclamation-triangle text-warning" role="img" aria-label="Warned user"></i>
                            @endif
                            @if($user->donor === 'yes')
                                <i class="bi bi-piggy-bank text-warning" role="img" aria-label="Donor"></i>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="chat-presence-empty mb-0" id="online-member-empty" role="status" hidden>No online members match your search.</p>
        @endif
    </div>
</details>

<style>
.chat-member-tooltip .tooltip-inner {
    max-width: 280px;
    padding: .65rem .85rem;
    border: 1px solid rgba(99, 210, 198, .2);
    border-radius: 10px;
    color: #cbd5e1;
    background: #0a0f1b;
    box-shadow: 0 10px 25px rgba(0, 0, 0, .3);
    text-align: left;
    overflow-wrap: anywhere;
    font-size: 12px;
    line-height: 1.7;
}
.chat-member-tooltip .tooltip-inner strong { color: var(--ui-accent, #63d2c6); }
.chat-member-tooltip { --bs-tooltip-bg: #0a0f1b; --bs-tooltip-opacity: 1; }

#community-chat .chat-presence {
    border: 1px solid rgba(99, 210, 198, .16);
    border-radius: 14px;
    background: linear-gradient(115deg, rgba(99, 210, 198, .075), rgba(10,15,27,.28));
    overflow: hidden;
}
#community-chat .chat-presence-summary {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .8rem 1rem;
    cursor: pointer;
    list-style: none;
}
#community-chat .chat-presence-summary::-webkit-details-marker { display: none; }
#community-chat .chat-presence-summary:focus-visible,
#community-chat .chat-member:focus-visible {
    outline: 2px solid var(--ui-accent, #63d2c6);
    outline-offset: -2px;
    border-radius: 12px;
}
#community-chat .chat-presence-status {
    width: 8px;
    height: 8px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #63d2a0;
    box-shadow: 0 0 0 4px rgba(99, 210, 160, .1), 0 0 14px rgba(99, 210, 160, .3);
}
#community-chat .chat-presence-heading { color: #e2edf4; font-size: 13px; font-weight: 700; }
#community-chat .chat-presence-count {
    display: inline-block;
    margin-left: .3rem;
    padding: .1rem .45rem;
    border-radius: 6px;
    color: var(--ui-accent, #63d2c6);
    background: rgba(99, 210, 198, .12);
    font-size: 11px;
}
#community-chat .chat-presence-preview { display: flex; margin-left: auto; padding-left: .4rem; }
#community-chat .chat-presence-preview > span {
    display: grid;
    place-items: center;
    width: 27px;
    height: 27px;
    margin-left: -.4rem;
    border: 2px solid #172333;
    border-radius: 50%;
    color: var(--member-color, #a6bdce);
    background: #17212c;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}
#community-chat .chat-presence-preview > .chat-presence-overflow { width: auto; min-width: 30px; padding: 0 .3rem; }
#community-chat .chat-presence-chevron { font-size: 12px; color: #91a8bb; transition: transform .18s ease; }
#community-chat .chat-presence[open] .chat-presence-chevron { transform: rotate(180deg); }
#community-chat .chat-presence-body { padding: 0 1rem .9rem; }
#community-chat .chat-presence-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: .65rem; margin-bottom: .75rem; color: #91a8bb; font-size: 12px; }
#community-chat .chat-presence-search { display: flex; align-items: center; gap: .45rem; margin-left: auto; padding: .35rem .6rem; border: 1px solid rgba(148, 163, 184, .16); border-radius: 8px; background: rgba(10,15,27,.4); }
#community-chat .chat-presence-search:focus-within { border-color: var(--ui-accent, #63d2c6); }
#community-chat .chat-presence-search input { width: 135px; min-width: 0; border: 0; outline: 0; color: #e2edf4; background: transparent; font: inherit; }
#community-chat .chat-presence-search input::placeholder { color: #91a8bb; }
#community-chat .chat-presence-members { display: flex; flex-wrap: wrap; gap: .45rem; list-style: none; padding: 0; margin: 0; max-height: 150px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #375560 transparent; }
#community-chat .chat-presence-members li { max-width: 100%; min-width: 0; }
#community-chat .chat-member { display: flex; align-items: center; gap: .4rem; max-width: 100%; padding: .3rem .6rem .3rem .3rem; color: var(--member-color, #cbd5e1); background: rgba(148,163,184,0.0385); border: 1px solid rgba(148, 163, 184, .1); border-radius: 9px; text-decoration: none; font-size: 12px; font-weight: 600; transition: background .18s ease, border-color .18s ease; }
#community-chat .chat-member:hover { background: rgba(99, 210, 198, .1); border-color: rgba(99, 210, 198, .3); }
#community-chat .chat-member-initial { display: grid; place-items: center; flex-shrink: 0; width: 24px; height: 24px; border-radius: 7px; background: rgba(148,163,184,0.084); text-transform: uppercase; }
#community-chat .chat-member-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
#community-chat .chat-member-you { color: #9aafbf; font-size: 10px; font-weight: 400; }
#community-chat .chat-presence-empty { color: #91a8bb; font-size: 12px; padding: .4rem 0; }
@media (max-width: 575.98px) {
    #community-chat .chat-presence-summary { padding: .7rem .75rem; }
    #community-chat .chat-presence-body { padding: 0 .75rem .75rem; }
    #community-chat .chat-presence-toolbar > span { display: none; }
    #community-chat .chat-presence-search { width: 100%; }
    #community-chat .chat-presence-search input { width: 100%; }
    #community-chat .chat-presence-preview > span:nth-child(n+4):not(:last-child) { display: none; }
    #community-chat .chat-presence-members { max-height: 120px; }
}
@media (prefers-reduced-motion: reduce) {
    #community-chat .chat-presence-chevron, #community-chat .chat-member { transition: none; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('online-member-search');
    const list = document.getElementById('online-users-list');
    const empty = document.getElementById('online-member-empty');
    if (!search || !list || !empty) return;

    search.addEventListener('input', function () {
        const query = search.value.trim().toLocaleLowerCase();
        let visible = 0;
        list.querySelectorAll('[data-member-name]').forEach(function (member) {
            const matches = member.dataset.memberName.toLocaleLowerCase().includes(query);
            member.hidden = !matches;
            if (matches) visible++;
        });
        empty.hidden = visible > 0;
    });
});
</script>
