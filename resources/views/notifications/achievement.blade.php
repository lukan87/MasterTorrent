<i class="bi bi-trophy-fill text-warning me-1" aria-hidden="true"></i>
<strong>{{ $data['title'] ?? 'Achievement unlocked' }}</strong>
<div class="small mt-1">{{ $data['message'] ?? 'Congratulations on your achievement!' }}</div>
<div class="small text-info mt-1">
    +{{ number_format($data['bonus'] ?? 0, 2) }} bonus points
    @if(($data['invites'] ?? 0) > 0)
        · +{{ $data['invites'] }} {{ \Illuminate\Support\Str::plural('invite', $data['invites']) }}
    @endif
</div>
