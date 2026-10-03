<i class="bi {{ $data['type'] === 'torrent_comment' ? 'bi-chat-dots-fill' : 'bi-emoji-smile' }} text-info me-1" aria-hidden="true"></i>
<strong>{{ $data['author'] ?? 'Someone' }}</strong>
@if($data['type'] === 'torrent_comment')
    commented on your torrent
@else
    reacted {{ $data['reaction'] ?? '' }} to your torrent
@endif
<strong>{{ $data['torrent_name'] ?? 'Unknown' }}</strong>
