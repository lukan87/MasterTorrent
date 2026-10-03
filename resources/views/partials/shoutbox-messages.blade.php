@if(auth()->check() && auth()->user()->chatblock)

@php

    $systemUser = \App\Models\User::find(2);

    $classColor = \App\Models\UserClass::getClassColor($systemUser->user_class ?? 0);

@endphp

<div class="message system" data-id="system">

    <img class="avatar"

         src="{{ $systemUser->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

    <div class="bubble" style="--accent: {{ $classColor }}">

        <div class="header d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-2">

                <span class="username"

                      style="color: {{ $classColor }}">

                    {{ $systemUser->name ?? 'System' }}

                </span>

            </div>

            <span class="badge time-badge">

                <i class="bi bi-shield-lock me-1"></i>

                System

            </span>

        </div>

        <div class="content fs-5">

            ⚠️ Unable to post or see chat messages.  

            Your chat access has been restricted by staff.

        </div>

    </div>

</div>

@else

{{-- NORMAL CHAT MESSAGES --}}

@php $prevUserId = null; @endphp

@forelse($messages->sortBy([['created_at', 'asc'], ['id', 'asc']]) as $message)

            @php

                $classColor = \App\Models\UserClass::getClassColor($message->user->user_class);
                $grouped = ($prevUserId === $message->user_id && $message->user_id != 2);

            @endphp



              <div id="shout-{{ $message->id }}" class="message {{ auth()->id() === $message->user_id ? 'own' : '' }}{{ $grouped ? ' grouped' : '' }}" data-id="{{ $message->id }}" data-user="{{ $message->user_id }}" data-sticky="{{ $message->sticky ? 1 : 0 }}">

                <img class="avatar"

                     src="{{ $message->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

                <div class="bubble" style="--accent: {{ $classColor }}">

                   <div class="header d-flex align-items-center justify-content-between">

                {{-- LEFT SIDE: Username + Actions --}}

               <div class="d-flex align-items-center gap-2">

           {{-- Username --}}

           <a href="{{ route('profile.show', $message->user->id) }}"

           class="username d-inline-flex align-items-center gap-2"

           style="color: {{ $classColor }}"

           data-bs-toggle="tooltip"

           title="{{ $message->user->role_name }}">

            <span>{{ $message->user->name }}</span>

            <span class="role-badge role-{{ Str::slug($message->user->role_name) }}">

                @switch($message->user->role_name)

                    @case('Web Developer') <i class="bi bi-code-slash fs-5"></i> @break

                    @case('Owner') <i class="bi bi-emoji-sunglasses-fill"></i> @break

                    @case('Admin') <i class="bi bi-shield-fill-check"></i> @break

                    @case('Moderator') <i class="bi bi-shield-lock-fill"></i> @break

                    @case('VIP') <i class="bi bi-gem"></i> @break

                    @case('Elite User') <i class="bi bi-stars"></i> @break

                    @case('Special User') <i class="bi bi-lightning-fill"></i> @break

                    @case('Uploader') <i class="bi bi-cloud-arrow-up-fill"></i> @break

                    @default <i class="bi bi-person-fill"></i>

                @endswitch

            </span>

         </a>

          {{-- Actions (NOW INLINE) --}}

          <div class="actions-inline d-flex align-items-center gap-1">

            {{-- Reply --}}

            @php

$isSystem = $message->user_id == 2;

@endphp

@if(!$isSystem)

<button class="btn-icon"

        onclick="toggleReplyForm({{ $message->id }})"

        data-bs-toggle="tooltip"

        title="Reply">

    <i class="bi bi-reply fs-5"></i>

</button>

@endif

            @if(auth()->id() === $message->user_id || Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

                {{-- Edit --}}

                <button type="button"

                        class="btn-icon warn"

                        data-bs-toggle="tooltip"

                        title="Edit"

                        onclick="openEdit({{ $message->id }})">

                    <i class="bi bi-pencil fs-5"></i>

                </button>

                 {{-- Sticky (ADMIN+) --}}
                 @if(Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                 <form action="{{ route('shoutbox.sticky', $message->id) }}"
                       method="POST"
                       class="d-inline">
                     @csrf
                     <button class="btn-icon {{ $message->sticky ? 'text-warning' : 'text-muted' }}"
                             data-bs-toggle="tooltip"
                             title="{{ $message->sticky ? 'Unstick' : 'Sticky' }}">
                         <i class="bi bi-pin-fill fs-5"></i>
                     </button>
                 </form>
                 @endif

                 {{-- Delete --}}
                 @if(Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                 <form action="{{ route('shoutbox.destroy', $message->id) }}"

                      method="POST"

                      class="shoutbox-delete-form d-inline"

                      data-id="{{ $message->id }}">

                    @csrf

                    @method('DELETE')

                    <button class="btn-icon danger fs-5"

                            data-bs-toggle="tooltip"

                            title="Delete"

                            >

                        <i class="bi bi-trash"></i>

                    </button>

                </form>
                 @endif

            @endif

              </div>

            </div>

                 
{{-- RIGHT SIDE: Time --}}

               <span class="badge time-badge">
                @if($message->sticky)
                  <span class="badge text-warning"><i class="bi bi-pin-fill"></i> Pinned</span>
                @endif

               <i class="bi bi-clock me-1 fs-6"></i>

              <time class="ts" datetime="{{ $message->created_at->toIso8601String() }}" title="{{ $message->created_at->format('Y-m-d H:i') }}">

                     {{ \App\Helpers\FormatHelper::shortRelativeTime($message->created_at) }}

                    </time>

                </span>

                    </div>

                    <div class="content fs-6">

                        {!! convertCustomTagsToHtml($message->message) !!}

                    </div>

                                  {{-- EDIT FORM (MESSAGE) --}}

                        <div class="edit-form mt-2" id="edit-form-{{ $message->id }}" style="display:none;">

                      <form class="shoutbox-edit-form"

                        data-id="{{ $message->id }}"

                            action="{{ route('shoutbox.update', $message->id) }}"

                     method="POST">

                       @csrf

                     @method('PUT')

                     <textarea name="content"

                  class="edit-textarea"

                  rows="3"

                  maxlength="1000" required>{{ $message->message }}</textarea>

                <div class="d-flex gap-2 mt-2">

                     <button class="btn btn-sm btn-success">Save</button>

                       <button type="button"

                    class="btn btn-sm btn-secondary"

                    onclick="closeEdit({{ $message->id }})">

                Cancel

                  </button>

                </div>

                 </form>

               </div>



{{-- Reply form --}}

<form id="reply-form-{{ $message->id }}"

      action="{{ route('shoutbox.reply', $message->id) }}"

      method="POST"

      class="reply-form shoutbox-reply-form"

      data-parent="{{ $message->id }}"

      style="display: none;">

    @csrf

    <textarea name="content"

              class="reply-textarea"

              placeholder="Reply..." maxlength="400"

              required></textarea>

    <div class="reply-actions">

        <button type="submit" class="btn btn-sm btn-success">

            <i class="bi bi-send me-1"></i> Send

        </button>

        <button type="button"

                class="btn btn-sm btn-outline-light"

                onclick="toggleReplyForm({{ $message->id }})">

            Cancel

        </button>

    </div>

</form>





                    {{-- Replies --}}

                    @if($message->replies->count())

                        <div class="reply-timeline-heading mt-3">
                            <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                            {{ $message->replies->count() }} {{ $message->replies->count() === 1 ? 'reply' : 'replies' }}
                        </div>
                        <div class="replies reply-timeline" role="list" aria-label="Replies, oldest first">

                            @foreach($message->replies->sortBy('created_at') as $reply)

                                @php

                                    $replyColor = \App\Models\UserClass::getClassColor($reply->user->user_class);

                                @endphp

                                <div id="shout-{{ $reply->id }}" class="reply-card {{ auth()->id() === $reply->user_id ? 'own' : '' }}" data-user="{{ $reply->user_id }}" role="listitem" style="--reply-accent: {{ $replyColor }}">

                                    <img class="avatar-sm"

                                         src="{{ $reply->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

                                    <div class="reply-bubble glass" style="--accent: {{ $replyColor }}">

                                       <div class="reply-header d-flex align-items-center justify-content-between">

    {{-- Left: username + role --}}

<strong class="reply-username d-inline-flex align-items-center gap-2">

    <a href="{{ route('profile.show', $reply->user->id) }}"

       class="d-inline-flex align-items-center gap-2 text-decoration-none"

       style="color: {{ $replyColor }}"

       data-bs-toggle="tooltip"

       title="{{ $reply->user->role_name }}">

        {{-- Username --}}

        <span>{{ $reply->user->name }}</span>
        @if(auth()->id() === $reply->user_id)
            <span class="reply-you-label">You</span>
        @endif

        {{-- Role badge --}}

        <span class="role-badge role-{{ Str::slug($reply->user->role_name) }}">

            @switch($reply->user->role_name)

                @case('Owner')

                    <i class="bi bi-emoji-sunglasses-fill"></i>

                    @break

                @case('Admin')

                    <i class="bi bi-shield-fill-check"></i>

                    @break

                @case('Web Developer')

                    <i class="bi bi-code-slash"></i>

                    @break

                @case('Moderator')

                    <i class="bi bi-shield-lock-fill"></i>

                    @break

                @case('VIP')

                    <i class="bi bi-gem"></i>

                    @break

                @case('Elite User')

                    <i class="bi bi-stars"></i>

                    @break

                @case('Special User')

                    <i class="bi bi-lightning-fill"></i>

                    @break

                @case('Uploader')

                    <i class="bi bi-cloud-arrow-up-fill"></i>

                    @break

                @default

                    <i class="bi bi-person-fill"></i>

            @endswitch

        </span>

    </a>

</strong>



    {{-- Right: meta + actions --}}

    <div class="reply-meta d-flex align-items-center gap-2">

<span class="badge time-badge time-badge-sm">

    <time class="ts" datetime="{{ $reply->created_at->toIso8601String() }}" title="{{ $reply->created_at->format('Y-m-d H:i') }}">

        {{ \App\Helpers\FormatHelper::shortRelativeTime($reply->created_at) }}

    </time>

    @if(

        auth()->user()->user_class >= \App\Models\UserClass::MODERATOR &&

        $reply->updated_at &&

        $reply->updated_at->gt($reply->created_at)

    )

        <span class="edited-badge ms-1"

              data-bs-toggle="tooltip"

              title="Edited at {{ $reply->updated_at->format('Y-m-d H:i') }}">

            (edited)

        </span>

    @elseif(auth()->user()->user_class >= \App\Models\UserClass::MODERATOR)

        <span class="edited-badge ms-1 d-none"

              data-bs-toggle="tooltip">

            (edited)

        </span>

    @endif

</span>







        {{-- Edit --}}

        @if(

            auth()->id() === $reply->user_id ||

            Auth::user()->user_class >= \App\Models\UserClass::MODERATOR

        )

            <button type="button"

        class="btn-icon warn"

        data-bs-toggle="tooltip"

        title="Edit reply"

        onclick="openReplyEdit({{ $reply->id }})">

    <i class="bi bi-pencil"></i>

</button>

        @endif

        {{-- Delete --}}

        @if(Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

           <form action="{{ route('shoutbox.destroy', $reply->id) }}"

      method="POST"

      class="reply-delete-form d-inline"

      data-bs-toggle="tooltip" title="Delete message"

      data-id="{{ $reply->id }}">

    @csrf

    @method('DELETE')

    <button type="button"

            class="btn-icon danger"

            title="Delete reply"

            onclick="handleReplyDelete(event, this.form)">

        <i class="bi bi-trash"></i>

    </button>

</form>

        @endif

    </div>

</div>



                                        <div class="reply-content-wrapper" data-id="{{ $reply->id }}">

    {{-- DISPLAY --}}

    <div class="reply-content">

        {!! convertCustomTagsToHtml($reply->message) !!}

    </div>

    {{-- EDIT FORM (SAME CLASS AS MESSAGE EDIT) --}}

    <div class="edit-form mt-2" id="reply-edit-form-{{ $reply->id }}" style="display:none;">

        <form class="shoutbox-edit-form"

              data-id="{{ $reply->id }}"

              action="{{ route('shoutbox.update', $reply->id) }}"

              method="POST">

            @csrf

            @method('PUT')

            <textarea name="content"

                      class="edit-textarea"

                      rows="3"

                      maxlength="1000" required>{{ $reply->message }}</textarea>

            <div class="d-flex gap-2 mt-2">

                <button class="btn btn-sm btn-success">Save</button>

                <button type="button"

                        class="btn btn-sm btn-secondary"

                        onclick="closeReplyEdit({{ $reply->id }})">

                    Cancel

                </button>

            </div>

        </form>

    </div>

</div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif

                </div>

            </div>

        @php $prevUserId = $message->user_id; @endphp

        @empty

            <div class="shoutbox-empty">
                <i class="bi bi-chat-dots" style="font-size:2.5rem;opacity:.35;"></i>
                <p style="margin:0;opacity:.55;font-size:14px;">No messages yet. Be the first to say hello! &#x1F44B;</p>
            </div>

        @endforelse

        @endif

