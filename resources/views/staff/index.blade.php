@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Staff Members</h1>

    @php
        // Get all roles and define a custom order
        $roles = App\Models\UserClass::getClasses();
        $customOrder = [
            App\Models\UserClass::OWNER,
            App\Models\UserClass::ADMIN,
            App\Models\UserClass::MODERATOR,
        ];

        // Add the remaining roles not in customOrder
        $remainingRoles = array_diff(array_keys($roles), $customOrder);
        $orderedRoles = array_merge($customOrder, $remainingRoles);
    @endphp

    @foreach ($orderedRoles as $classId)
        @php
            $roleName = $roles[$classId] ?? null;
            $roleMembers = $staff->where('user_class', $classId);
        @endphp

        @if ($roleName && $roleMembers->isNotEmpty())
            <h2 class="my-4">{{ $roleName }}</h2>

            <div class="row g-3">
                @foreach ($roleMembers as $member)
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="row g-0 h-100">
                            <div class="col-md-4 d-flex align-items-center justify-content-center">
                                <img src="{{ $member->profile_image ?? asset('images/default-profile.png') }}"
                                     class="img-fluid rounded-circle"
                                     alt="{{ $member->name }}"
                                     style="width: 100px; height: 100px;">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title" style="color: {{ App\Models\UserClass::getClassColor($member->user_class) }};">
                                        {{ $member->name }}
                                    </h5>

                                    <a href="{{ route('messages.create', ['receiver_id' => $member->id]) }}" class="text-light">
                              <button class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Send message to {{$member->name}}">Send message</button>
                       </a>
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
@endsection
