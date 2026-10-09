                @if($hasOlder)
                    <div class="ms-load-older">
                        <a href="{{ route('conversations.show', $activeConversation->id) }}?before={{ $messages->first()->id }}"
                           class="ms-older-link">
                            <i class="bi bi-arrow-up-circle me-1"></i>Load earlier messages
                        </a>
                    </div>
                @endif

                @foreach($messages as $message)
                    @if(
                        $loop->first ||
                        $message->created_at->format('Y-m-d') !== $messages[$loop->index - 1]->created_at->format('Y-m-d')
                    )
                        @php
                            $dayLabel = $message->created_at->isToday()
                                ? 'Today'
                                : ($message->created_at->isYesterday()
                                    ? 'Yesterday'
                                    : $message->created_at->format('d M Y'));
                        @endphp
                        <div class="ms-day-divider"><span>{{ $dayLabel }}</span></div>
                    @endif

                    <div class="ms-msg-row {{ $message->sender_id == Auth::id() ? 'me' : 'them' }}">
                        @if($message->sender_id != Auth::id())
                            <img class="ms-msg-avatar"
                                 src="{{ $message->sender->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="">
                        @endif
                        <div class="ms-msg-bubble" data-id="{{ $message->id }}" data-body="{{ $message->body }}">
                            @include('messages.mass-pill', ['message' => $message, 'showMassActions' => true])
                            <div class="ms-msg-content">{!! convertCustomTagsToHtml($message->body) !!}</div>
                            <div class="ms-msg-footer">
                                @if($message->sender_id == Auth::id())
                                    <button type="button" class="ms-msg-action edit-msg" data-url="{{ route('messages.edit', $message) }}" title="Edit message" aria-label="Edit message"><i class="bi bi-pencil"></i></button>
                                @endif
                                @if(Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                    <button type="button" class="ms-msg-action delete-msg" data-url="{{ route('messages.delete', $message) }}" title="Delete message for both participants" aria-label="Delete message"><i class="bi bi-trash3"></i></button>
                                @endif
                                <span class="ms-msg-time">{{ $message->created_at->format('d M Y · H:i') }}</span>
                                @if($message->sender_id == Auth::id())
                                    <span class="ms-receipt {{ $message->is_read ? 'is-read' : '' }}" data-receipt="{{ $message->id }}" title="{{ $message->is_read ? 'Opened by the recipient' : 'Sent; recipient has not opened it yet' }}">
                                        <i class="bi {{ $message->is_read ? 'bi-check2-all' : 'bi-check2' }}"></i><span>{{ $message->is_read ? 'Read' : 'Sent' }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($message->sender_id == Auth::id())
                            <img class="ms-msg-avatar"
                                 src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="">
                        @endif
                    </div>
                @endforeach