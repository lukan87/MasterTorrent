<div class="card mt-3 mb-3">
    <div class="card-header">
        <h5>Online Users</h5>
    </div>
    <div class="card-body">
    @if ($onlineUsers->isEmpty())
    <p>No users are currently online.</p>
@else
    <p>
    @foreach ($onlineUsers as $user)
    <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}" 
       style="color: {{ \App\Models\UserClass::getClassColor($user->user_class) }}">
        {{ $user->name }}
    </a>
    ({{ $user->role_name }})
    {{ !$loop->last ? ',' : '' }}
@endforeach

    </p>
@endif

    </div>
</div>
