<div class="container mt-3">
    <div class="card mt-3 mb-3">
        <div class="card-header">
            <h5>Online Users ({{ $onlineUserCount }})</h5>
        </div>
        <div class="card-body">
            @if ($onlineUsers->isEmpty())
                <p>No users are currently online.</p>
            @else
                <div class="row">
                    <div class="col-12 mb-3">
                        <p id="online-users-list">
                            @foreach ($onlineUsers as $index => $user)
                                <span class="online-user {{ $index >= 100 ? 'd-none extra-user' : '' }}">
                                    <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}"
                                       style="color: {{ \App\Models\UserClass::getClassColor($user->user_class) }}" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-html="true"
                                       data-bs-title='
                                           <div class="card p-2" style="width: 300px;">
                                               <div class="card-body text-center">
                                                   <h6 class="mb-1">{{ \App\Models\UserClass::getClassName($user->user_class) }}</h6>
                                                   <p class="mb-0"><strong>Up: {{ App\Helpers\FormatHelper::formatSize($user->uploaded) }} </strong></p>
                                                   <p class="mb-0"><strong>Down: {{ App\Helpers\FormatHelper::formatSize($user->downloaded) }} </strong></p>
                                               </div>
                                           </div>'>
                                        {{ $user->name }}
                                        @if($user->warned)
                                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                        @endif
                                        @if($user->donor == 'yes')
                                            <i class="bi bi-star-fill text-success"></i>
                                        @endif
                                    </a>{{ !$loop->last ? ',' : '' }}
                                </span>
                            @endforeach
                        </p>

                        @if($onlineUsers->count() > 100)
                            <button id="show-more-btn" class="btn btn-link text-decoration-none">
                                <i class="fas fa-chevron-down"></i> Show More
                            </button>
                        @endif
                    </div>

                    <!-- User Classes Section -->
                    <div class="col-12">
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
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const showMoreBtn = document.getElementById("show-more-btn");
    if (showMoreBtn) {
        showMoreBtn.addEventListener("click", function() {
            const extraUsers = document.querySelectorAll(".extra-user");
            const icon = showMoreBtn.querySelector("i");

            // Toggle visibility
            extraUsers.forEach(el => el.classList.toggle("d-none"));

            // Toggle button text & icon
            if (extraUsers[0].classList.contains("d-none")) {
                showMoreBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Show More';
            } else {
                showMoreBtn.innerHTML = '<i class="fas fa-chevron-up"></i> Show Less';
            }
        });
    }
});
</script>
