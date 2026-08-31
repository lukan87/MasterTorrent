@php
    $replyColor = \App\Models\UserClass::getClassColor($reply->user->user_class);
@endphp

<div class="reply-card">
    <img class="avatar-sm"
         src="{{ $reply->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

    <div class="reply-bubble glass" style="--accent: {{ $replyColor }}">
        <div class="reply-header">
            <strong class="reply-username" style="color: {{ $replyColor }}">
                {{ $reply->user->name }}
            </strong>

            <span class="timestamp">
                {{ $reply->created_at->format('H:i') }}
            </span>
        </div>

        <div class="reply-content">
            {!! convertCustomTagsToHtml($reply->message) !!}
        </div>
    </div>
</div>
