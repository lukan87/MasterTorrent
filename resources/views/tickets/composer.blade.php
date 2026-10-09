@php($isStaff = auth()->user()->user_class > 5)
            <section class="support-panel" data-ticket-composer aria-labelledby="reply-title">
                @if(in_array($ticket->status, ['Resolved', 'Closed'], true) && !$isStaff)
                    <div class="support-panel-body">
                        <h2 id="reply-title"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>Ticket {{ strtolower($ticket->status) }}</h2>
                        <p class="support-help mb-3">This ticket has been {{ strtolower($ticket->status) }} by the support team and no longer accepts replies. If you need more help, please create a new ticket.</p>
                        <a class="support-btn support-btn-primary" href="{{ route('tickets.create') }}">Create a new ticket</a>
                    </div>
                @elseif($ticket->is_locked)
                    <div class="support-panel-body"><h2 id="reply-title"><i class="bi bi-lock me-2" aria-hidden="true"></i>Conversation locked</h2><p class="support-help mb-3">Replies are paused. Unlock this ticket to follow up on the same issue. For a different issue, create a new ticket.</p><form method="POST" action="{{ route('tickets.unlock', $ticket->id) }}">@csrf<button class="support-btn" type="submit">Unlock conversation</button></form></div>
                @else
                    <div class="support-panel-head"><h2 id="reply-title">Add a reply</h2><small id="reply-audience">Visible to the member and support team</small></div>
                    <form method="POST" action="{{ route('tickets.reply', $ticket->id) }}" enctype="multipart/form-data" class="support-panel-body">
                        @csrf
                        @if($isStaff)<label class="support-note-toggle mb-3" for="staff-note"><input id="staff-note" type="checkbox" name="staff_note" value="1" @checked(old('staff_note'))>Internal note — visible only to staff</label>@endif
                        <div class="support-field"><label for="message">Message</label><textarea id="message" name="message" class="form-control" rows="6" maxlength="20000" placeholder="Write your reply…" required>{{ old('message') }}</textarea></div>
                        @include('tickets.partials.upload')
                        <button id="send-reply" class="support-btn support-btn-primary" type="submit">Send reply</button>
                    </form>
                @endif
            </section>