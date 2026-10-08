@php($broadcast = $message->massDelivery?->massMessage)
@if($broadcast)
    <div class="message-kind-wrap">
        <span class="message-kind-pill message-kind-mass"><i class="bi bi-broadcast" aria-hidden="true"></i>Mass message · sent to {{ number_format($broadcast->delivered_count) }} {{ (int) $broadcast->delivered_count === 1 ? 'user' : 'users' }}</span>
        @if(($showMassActions ?? false) && auth()->user()->user_class >= \App\Models\UserClass::ADMIN)
            <a class="message-kind-pill" href="{{ route('admin.users.mass-messages.show', $broadcast) }}">Delivery details</a>
            <form method="POST" action="{{ route('admin.users.mass-messages.destroy', $broadcast) }}" onsubmit="return confirm('Delete this mass message for all recipients? Pending deliveries will stop. Other messages in these conversations will be kept.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="message-kind-pill message-kind-delete">Delete all recipient copies</button>
            </form>
        @endif
    </div>
@elseif($showNormal ?? false)
    <span class="message-kind-pill">Normal message</span>
@endif
