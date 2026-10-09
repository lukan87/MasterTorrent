@if($subscribeAvailable)
    <form action="{{ route($isSubscribed ? 'torrents.unsubscribe' : 'torrents.subscribe', $torrent->id) }}"
          method="POST" data-torrent-subscription-action>
        @csrf
        <button type="submit"
                class="btn modern-action-btn {{ $isSubscribed ? 'unsubscribe-btn' : 'subscribe-btn' }}"
                data-bs-toggle="tooltip"
                title="{{ $isSubscribed ? 'Stop receiving notifications when a new version of this title is uploaded' : 'Get notified whenever a new version of this title is uploaded' }}">
            <i class="bi {{ $isSubscribed ? 'bi-bell-fill' : 'bi-bell' }} me-1"></i>
            {{ $isSubscribed ? 'Subscribed' : 'Subscribe' }}
        </button>
    </form>
@endif

{{-- Keep the existing visibility rule for the subscriber count and names. --}}
@if(!empty($torrent->tmdbid))
    @include('torrents.partials._subscribers-label', ['subscribers' => $subscribers ?? collect()])
@endif
