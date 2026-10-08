@if($user->invited_by || $user->invitees_count > 0)
<div class="modal fade profile-guide-modal invitation-tree-modal" id="invitationTreeModal" tabindex="-1" aria-labelledby="invitationTreeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="seeder-guide-eyebrow"><i class="bi bi-diagram-3" aria-hidden="true"></i> Growing the community</span>
                    <h2 class="modal-title" id="invitationTreeModalTitle">Invitation Tree</h2>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($user->invited_by)
                    <div class="invitation-tree-origin">
                        <span class="invitation-tree-label">Invited by</span>
                        <div>
                            @if($user->inviter)
                                <a href="{{ route('profile.show', ['id' => $user->inviter->id, 'name' => $user->inviter->name]) }}">{{ $user->inviter->name }}</a>
                                @if($user->inviter->trashed()) <span class="invitation-tree-deleted">Deleted account</span> @endif
                            @else
                                <span>Unavailable member</span>
                            @endif
                            <i class="bi bi-arrow-right mx-2" aria-hidden="true"></i>
                            <strong>{{ $user->name }}</strong>
                        </div>
                    </div>
                @endif
                <div class="seeder-guide-current">
                    <span>Members invited by {{ $user->name }}</span>
                    <strong>{{ number_format($user->invitees_count) }} {{ \Illuminate\Support\Str::plural('member', $user->invitees_count) }}</strong>
                </div>
                @if($user->invitees_count > 0)
                    <ul class="invitation-tree-members">
                        @foreach($inviteTreeMembers as $member)
                            <li>
                                <a href="{{ route('profile.show', ['id' => $member->id, 'name' => $member->name]) }}">
                                    <span class="invitation-tree-avatar" aria-hidden="true">{{ mb_substr($member->name, 0, 1) }}</span>
                                    <span class="invitation-tree-member-name">{{ $member->name }} @if($member->trashed())<small class="invitation-tree-deleted">Deleted account</small>@endif</span>
                                    <i class="bi bi-arrow-up-right ms-auto" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="invitation-tree-pagination mt-3">{{ $inviteTreeMembers->fragment('invitation-tree')->links() }}</div>
                @else
                    <div class="invitation-tree-empty"><i class="bi bi-people" aria-hidden="true"></i><p>No members invited yet.</p></div>
                @endif
            </div>
            <div class="modal-footer"><button type="button" class="seeder-guide-close" data-bs-dismiss="modal">Back to profile</button></div>
        </div>
    </div>
</div>
<style>
.invitation-tree-card { width: 100%; text-align: left; font: inherit; cursor: pointer; }
.invitation-tree-card .invitation-tree-icon { color: var(--theme-blue-text, #acbaff); background: var(--theme-blue-soft, #acbaff12); }
.invitation-tree-card:focus-visible, .invitation-tree-modal a:focus-visible { outline: 2px solid var(--theme-teal-border, #80e0cf); outline-offset: 3px; }
.invitation-tree-origin { padding: 1.25rem; margin-bottom: 1rem; border: 1px solid var(--theme-blue-border, #acbaff30); background: var(--theme-blue-soft, #acbaff08); border-radius: 12px; overflow-wrap: anywhere; }
.invitation-tree-label { display: block; color: var(--theme-muted, #a5b4c7); font-size: var(--site-font-body, 13px); margin-bottom: .5rem; }
.invitation-tree-origin a { color: var(--theme-blue-text, #acbaff); }
.invitation-tree-deleted { color: var(--theme-muted, #a5b4c7); font-size: var(--site-font-body, 13px); }
.invitation-tree-members { list-style: none; margin: 0; padding: 0; display: grid; gap: .65rem; }
.invitation-tree-members a { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--theme-surface, #0e1822); color: var(--theme-text, #e9f0f7); border: 1px solid var(--theme-border, #2b3a4c); border-radius: 12px; text-decoration: none; }
.invitation-tree-members a:hover { border-color: var(--theme-teal-border, #80e0cf66); background: var(--theme-surface, #121f2a); }
.invitation-tree-avatar { display: grid; place-items: center; width: 40px; height: 40px; flex-shrink: 0; background: var(--theme-teal-soft, #80e0cf0d); color: var(--theme-teal-text, #80e0cf); border-radius: 10px; text-transform: uppercase; }
.invitation-tree-member-name { overflow-wrap: anywhere; min-width: 0; }
.invitation-tree-member-name small { display: block; }
.invitation-tree-empty { padding: 2rem 1rem; text-align: center; color: var(--theme-muted, #a5b4c7); }
.invitation-tree-empty > i { font-size: 2rem; color: var(--theme-blue-text, #acbaff); }
.invitation-tree-empty p { margin: .75rem 0 0; }
.invitation-tree-pagination { overflow-x: auto; }
</style>
@push('scripts')
<script>
(() => {
    const modal = document.getElementById('invitationTreeModal');
    if (!modal) return;
    document.body.appendChild(modal);
    const revealTree = () => {
        if (window.location.hash === '#invitation-tree' && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    };
    revealTree();
    window.addEventListener('hashchange', revealTree);
})();
</script>
@endpush
@endif
