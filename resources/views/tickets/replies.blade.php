                    @forelse($ticket->responses as $response)
                        <article class="support-message {{ $response->is_staff_note || $response->is_internal ? 'is-note' : '' }}" data-reply-id="{{ $response->id }}" @if($loop->last) id="latest-reply" @endif>
                            <div class="support-message-head"><div class="support-person"><span class="support-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($response->user?->name ?? '?', 0, 1)) }}</span><div><strong>{{ $response->user?->name ?? 'Deleted user' }}</strong> <span class="support-badge">{{ $response->is_staff_note || $response->is_internal ? 'Internal note' : (($response->user?->user_class ?? 0) > 5 ? 'Support team' : 'Member') }}</span></div></div><time class="support-muted" datetime="{{ $response->created_at->toIso8601String() }}" title="{{ $response->created_at->format('M j, Y H:i').' UTC' }}">{{ $response->created_at->diffForHumans() }}</time></div>
                            <div class="support-message-body">{{ $response->message }}</div>
                            @if($response->attachments->isNotEmpty())<div class="support-attachments">@foreach($response->attachments as $file)<a class="support-attachment" href="{{ route('tickets.download', $file->id) }}"><i class="bi bi-paperclip" aria-hidden="true"></i>{{ $file->file_name }}</a>@endforeach</div>@endif
                        </article>
                    @empty
                        <article class="support-message"><div class="support-message-body">{{ $ticket->description }}</div></article>
                    @endforelse