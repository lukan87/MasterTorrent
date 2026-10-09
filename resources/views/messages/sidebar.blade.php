        <div class="ms-conversation-scroll" id="conversationScroll">
            @forelse($conversations as $conv)
                @php
                    $other = Auth::id() == $conv->user_one ? $conv->userTwo : $conv->userOne;
                    $last  = $conv->lastMessage;
                    $unread = $conv->unread_count ?? 0;
                    $isActive = $activeConversation && $activeConversation->id === $conv->id;
                @endphp
                @if($last)
                    <a href="{{ route('conversations.show', $conv->id) }}"
                       class="ms-conv-item {{ $isActive ? 'active' : '' }}"
                       data-name="{{ strtolower($other->name ?? '') }}">
                        <div class="ms-avatar-wrap">
                            <img src="{{ $other->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                                 alt="{{ $other->name ?? 'Deleted member' }}" class="ms-avatar">
                            @if($unread > 0)
                                <span class="ms-unread-dot"></span>
                            @endif
                        </div>
                        <div class="ms-conv-body">
                            <div class="ms-conv-name">{{ $other->name ?? 'Unknown' }}</div>
                            @include('messages.mass-pill', ['message' => $last])
                            <div class="ms-conv-preview">
                                {{ Str::limit(strip_tags(convertCustomTagsToHtml($last->body)), 45) }}
                            </div>
                        </div>
                        <div class="ms-conv-meta">
                            <span class="ms-conv-time">{{ $last->created_at->diffForHumans() }}</span>
                            @if($unread > 0)
                                <span class="ms-badge">{{ $unread }}</span>
                            @endif
                        </div>
                    </a>
                @endif
            @empty
                <div class="ms-empty-state">
                    <i class="bi bi-chat-square-text"></i>
                    <p>No conversations yet</p>
                </div>
            @endforelse
        </div>

        @if($conversations->hasPages())
            <div class="ms-pagination">
                {{ $conversations->links('pagination::bootstrap-5') }}
            </div>
        @endif