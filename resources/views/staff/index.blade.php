@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-5 text-center display-5 text-light">Staff Members</h1>

    @php
        $roles = App\Models\UserClass::getClasses();
        $customOrder = [
            App\Models\UserClass::OWNER,
            App\Models\UserClass::ADMIN,
            App\Models\UserClass::MODERATOR,
            App\Models\UserClass::UPLOADER,
        ];
        $remainingRoles = array_diff(array_keys($roles), $customOrder);
        $orderedRoles = array_merge($customOrder, $remainingRoles);
    @endphp

    @foreach ($orderedRoles as $classId)
        @php
            $roleName = $roles[$classId] ?? null;
            $roleMembers = $staff->where('user_class', $classId);
        @endphp

        @if ($roleName && $roleMembers->isNotEmpty())
            <h2 class="my-4 text-light">{{ $roleName }}</h2>

            <div class="row g-4">
                @foreach ($roleMembers as $member)
                    @php 
                        $classColor = App\Models\UserClass::getClassColor($member->user_class);
                        $pulseClass = in_array($member->user_class, [App\Models\UserClass::OWNER, App\Models\UserClass::ADMIN]) ? 'pulse' : '';
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card staff-card h-100 border-0 shadow-sm position-relative">
                            <!-- Vertical color stripe with optional pulse -->
                            <div class="class-stripe {{ $pulseClass }}" 
                                 style="background-color: {{ $classColor }};
                                        --pulse-color: {{ $classColor }};">
                            </div>

                            <div class="row g-0 h-100 align-items-center">
                                <div class="col-4 d-flex align-items-center justify-content-center">
                                    <img src="{{ $member->profile_image ?? asset('images/default-profile.png') }}"
                                         class="img-fluid rounded-circle staff-avatar"
                                         alt="{{ $member->name }}"
                                         style="--avatar-glow: {{ $classColor }}">
                                </div>
                                <div class="col-8">
                                    <div class="card-body d-flex flex-column justify-content-center">
                                        <h5 class="card-title mb-2">
                                            <a href="{{ route('profile.show', ['id' => $member->id, 'name' => $member->name ?? 'Unknown']) }}"
                                               style="color: {{ $classColor }}; text-decoration: none;">
                                               {{ $member->name }}
                                            </a>
                                        </h5>
                                        <div class="mt-auto">
                                            <a href="{{ route('messages.create', ['receiver_id' => $member->id]) }}">
                                                <button class="btn btn-sm btn-success w-100 staff-btn" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Send message to {{ $member->name }}">
                                                    Send Message
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
</div>

<!-- Custom CSS -->
<style>
body {
    background: linear-gradient(135deg, #1c1c1c, #2c2c2c);
    color: #fff;
    font-family: 'Segoe UI', sans-serif;
}

.staff-card {
    border-radius: 12px;
    overflow: hidden;
    background: rgba(25, 25, 25, 0.85);
    transition: transform 0.2s, box-shadow 0.3s;
    position: relative;
}

.staff-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.5);
}

.staff-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid #444;
    /* Neon glow effect */
    box-shadow: 0 0 8px var(--avatar-glow, #fff55), 0 0 16px var(--avatar-glow, #fff44)33;
    transition: box-shadow 0.3s, transform 0.2s;
}

.staff-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 0 14px var(--avatar-glow, #fff), 0 0 28px var(--avatar-glow, #fff44);
}

.staff-btn {
    transition: transform 0.2s, background-color 0.2s;
}

.staff-btn:hover {
    transform: translateY(-2px);
    background-color: #28a745cc;
}

.class-stripe {
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    border-radius: 6px 0 0 6px;
    transition: box-shadow 0.3s;
}

/* Pulse animation using dynamic color */
@keyframes pulseGlow {
    0% { box-shadow: 0 0 8px 2px var(--pulse-color, #fff55); }
    50% { box-shadow: 0 0 14px 6px var(--pulse-color, #fff)66; }
    100% { box-shadow: 0 0 8px 2px var(--pulse-color, #fff55); }
}

.pulse {
    animation: pulseGlow 2s infinite ease-in-out;
}

.card-title a:hover {
    text-decoration: underline;
}
</style>
@endsection
