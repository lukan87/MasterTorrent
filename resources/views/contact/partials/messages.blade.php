@foreach($messages as $message)
    <article class="contact-message contact-message--{{ $message->sender_type === 'staff' ? 'staff' : 'guest' }}" data-contact-message="{{ $message->id }}" data-contact-id="{{ $message->contact_id }}">
        <div class="contact-message-meta"><strong>{{ $message->sender_type === 'staff' ? ($message->staff?->name ?? 'Staff') : 'Guest' }}</strong><time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('d M Y · H:i') }}</time></div>
        <div class="contact-message-text">{{ $message->message }}</div>
    </article>
@endforeach
