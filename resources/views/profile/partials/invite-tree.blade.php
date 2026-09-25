@if($user->invited_by || $user->invitees_count > 0)
    <section class="card mb-4" aria-label="Invitation tree">
        <div class="card-body">
            <h2 class="h5"><i class="bi bi-diagram-3 me-2"></i>Invitation tree</h2>
            @if($user->invited_by)
                <p>Invited by:
                    @if($user->inviter)
                        <a href="{{ route('profile.show', ['id' => $user->inviter->id, 'name' => $user->inviter->name]) }}">{{ $user->inviter->name }}</a>
                        @if($user->inviter->trashed()) <span class="text-muted">(deleted)</span> @endif
                    @else
                        <span class="text-muted">Unavailable member</span>
                    @endif
                    <span aria-hidden="true"> &rarr; </span>{{ $user->name }}
                </p>
            @endif
            @if($user->invitees_count > 0)
                <p class="mb-2">Members invited by {{ $user->name }} ({{ $user->invitees_count }}):</p>
                <ul>
                    @foreach($inviteTreeMembers as $member)
                        <li>
                            <a href="{{ route('profile.show', ['id' => $member->id, 'name' => $member->name]) }}">{{ $member->name }}</a>
                            @if($member->trashed()) <span class="text-muted">(deleted)</span> @endif
                        </li>
                    @endforeach
                </ul>
                {{ $inviteTreeMembers->links() }}
            @endif
        </div>
    </section>
@endif
