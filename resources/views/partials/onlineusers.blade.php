<div class="card mt-3 mb-3">
    <div class="card-header">
        <h5>Online Users ({{ $onlineUserCount }})</h5>
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
                    </a>{{ !$loop->last ? ',' : '' }}
                @endforeach
            </p>
            <hr>

            <p>
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
    @php
        $name = \App\Models\UserClass::getClassName($class);
        $color = \App\Models\UserClass::getClassColor($class);
    @endphp
    <span style="color: {{ $color }}">
        {{ $name }}
    </span>{{ !$loop->last ? ', ' : '' }}
@endforeach

            </p>
        @endif
    </div>
</div>
