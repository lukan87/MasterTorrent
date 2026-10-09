<div class="contact-list-meta"><span>{{ number_format($contacts->total()) }} requests</span><span>Page {{ $contacts->currentPage() }} of {{ $contacts->lastPage() }}</span></div>
@forelse($contacts as $contact)
    <a href="{{ route('contactstaff.show', $contact->id) }}" class="contact-request" data-contact-request="{{ $contact->id }}"><span class="contact-request-icon"><i class="bi {{ $contact->resolved ? 'bi-check2-circle' : 'bi-chat-left-text' }}" aria-hidden="true"></i></span><div class="contact-request-title"><strong>{{ $contact->subject }}</strong><span>{{ $contact->name }} · {{ $contact->email }}</span><small>{{ $contact->messages_count }} messages · {{ $contact->created_at->diffForHumans() }}</small></div><span class="contact-status" data-resolved="{{ $contact->resolved ? 'true' : 'false' }}">{{ $contact->resolved ? 'Resolved' : 'Open' }}</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
@empty
    <div class="contact-empty"><i class="bi bi-inbox" aria-hidden="true"></i><h2>All quiet here</h2><p>New contact requests will appear automatically.</p></div>
@endforelse
<div class="mt-3">{{ $contacts->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
