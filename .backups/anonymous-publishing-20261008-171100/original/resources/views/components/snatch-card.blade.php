

@php

$isSeeding = $history->seeder ?? false;

$statusClass = $isSeeding ? 'is-seeding' : 'not-seeding';

$ratio = $history->actual_downloaded > 0
? $history->uploaded / $history->actual_downloaded
: ($history->uploaded > 0 ? INF : 0);

$ratioDisplay = $history->actual_downloaded > 0
? number_format($ratio,2)
: '∞';

$ratioPercent = min(100,$ratio*100);

$remainingSeed = max(0,$requiredSeed - ($history->seedtime ?? 0));

$progress = min(100,(($history->seedtime ?? 0) / $requiredSeed) * 100);

/* DOWNLOAD PROGRESS */

$size = $history->torrent->size ?? 0;

$actualDownloaded = $history->actual_downloaded ?? $history->downloaded;

$seedtime = ($history->seedtime ?? 0) > 0
    ? $history->seedtime
    : ($history->total_seedtime ?? 0);

    $ratioMet = $ratio >= 1;
    $seedMet = $seedtime >= 43200;

// Detect completion properly (DB OR fallback)
$isCompleted = !empty($history->completed_at)
    || ($size > 0 && $actualDownloaded >= $size);

// Avoid 0B / not started torrents
$hasStarted = $actualDownloaded > 0;

// Final rule
$hasMetRequirements = $ratio >= 1 || $seedtime >= 43200;

$shouldWarn = !$isCompleted
    && $hasStarted
    && !$hasMetRequirements;

$downloadPercent = $size > 0
? ($actualDownloaded / $size) * 100
: 0;

$remainingDownload = max(0,$size - $actualDownloaded);

/* CATEGORY */

$cat = $history->torrent->category_id ?? 0;

$movieCategories=[1,2,5,6,9,10,11,12,16,17,18,19,24,25,31,32,54,55,81,82];
$tvCategories=[13,14,20,21];
$musicCategories=[28];
$gameCategories=[30,33];
$xxxCategories=[27,34];
$softwareCategories=[26];
$docCategories=[56,57];

$categoryIcon='bi-file-earmark';

if(in_array($cat,$movieCategories)) $categoryIcon='bi-film';
elseif(in_array($cat,$tvCategories)) $categoryIcon='bi-tv';
elseif(in_array($cat,$musicCategories)) $categoryIcon='bi-music-note';
elseif(in_array($cat,$gameCategories)) $categoryIcon='bi-controller';
elseif(in_array($cat,$xxxCategories)) $categoryIcon='bi-heart-fill';
elseif(in_array($cat,$softwareCategories)) $categoryIcon='bi-cpu';
elseif(in_array($cat,$docCategories)) $categoryIcon='bi-camera-reels';

$hnrDeadline = !empty($history->completed_at)
? $history->completed_at->copy()->addDays(7)
: null;

$isOwner = $history->torrent && $history->torrent->owner == $history->user_id;

@endphp


<div class="glass-card snatch-card mb-4 {{ $type=='hnr' ? 'not-seeding' : ($type=='seeding' ? 'is-seeding':'') }} {{ $statusClass }}">

<div class="row align-items-center">


{{-- CATEGORY --}}
@if($type=='snatchlist')
<div class="col-md-1 text-center category-icon">
<i class="bi {{ $categoryIcon }}"></i>
</div>
@endif



{{-- TORRENT INFO --}}
<div class="col-md-5">

<strong class="torrent-title">

@if($history->torrent)

<a href="{{ route('torrents.show',['id'=>$history->torrent->id]) }}">
<i class="bi bi-file-earmark-arrow-down"></i>
{{ $history->torrent->name }}
</a>

<div class="small text-muted mt-1">

<i class="bi bi-calendar3"></i>
Snatched: {{ $history->created_at->format('Y-m-d H:i') }}

<br>

@if(!empty($history->completed_at))

<i class="bi bi-check-circle"></i>
Completed: {{ $history->completed_at->format('Y-m-d H:i') }}

<br>

@endif

{{-- TORRENT STATUS MESSAGE --}}

@if($history->hitrun)

<div class="text-danger mt-1">
<i class="bi bi-exclamation-triangle-fill"></i>

<span data-bs-toggle="tooltip"
title="Continue seeding to cancel this Hit & Run">
<strong>Hit & Run</strong>
</span>
</div>


@elseif($isOwner)

<div class="text-info mt-1">

<i class="bi bi-person-check"></i>

<strong>Owner of this torrent</strong>

<div class="small text-muted">
You uploaded this torrent.
</div>

</div>


@elseif($history->completed_at && $history->hnr_satisfied)

<div class="text-success mt-1">

<i class="bi bi-check-circle-fill"></i>

<strong>Torrent completed.</strong>



@if($ratioMet && $seedMet)
    ✔ Ratio ≥ 1.00 and seedtime ≥ 12 hours completed
@elseif($ratioMet)
    ✔ Ratio ≥ 1.00 completed
@elseif($seedMet)
    ✔ Seedtime ≥ 12 hours completed
@endif

<br>

<div class="small text-muted">
You can delete it from your client or continue seeding to help the community.
</div>



</div>

@elseif($isCompleted && $hasMetRequirements)

<div class="text-success mt-1">

<i class="bi bi-check-circle-fill"></i>

<strong>Torrent requirement completed</strong>

<div class="small text-muted">




@if($ratioMet && $seedMet)
    ✔ Ratio ≥ 1.00 and seedtime ≥ 12 hours completed
@elseif($ratioMet)
    ✔ Ratio ≥ 1.00 completed
@elseif($seedMet)
    ✔ Seedtime ≥ 12 hours completed
@endif

<br>

Thank you for supporting the community ❤️

</div>

</div>

@elseif($shouldWarn)

<div class="text-warning mt-1">

<i class="bi bi-arrow-down-circle"></i>

<strong>This torrent must be completed and seeded to avoid Hit & Run</strong>

<div class="small">

Downloaded:
<strong>{{ \App\Helpers\FormatHelper::formatSize($history->downloaded) }}</strong>

{{ $history->completed_at 
    ? '| Completed at: '.$history->completed_at->format('Y-m-d H:i') 
    : '| Not completed yet' }}

@if(($history->actual_downloaded ?? 0) != $history->downloaded)
<br>
<span class="small text-muted">
Actual: {{ \App\Helpers\FormatHelper::formatSize($history->actual_downloaded) }}
</span>
@endif

<br>

Left to download:
<strong>{{ \App\Helpers\FormatHelper::formatSize($remainingDownload) }}</strong>

</div>

</div>


@elseif($hnrDeadline && !$hasMetRequirements)

<div class="text-warning mt-1">

<i class="bi bi-hourglass-split"></i>

You have until
<strong>{{ $hnrDeadline->format('Y-m-d H:i') }}</strong>

<div class="small">
{{ $hnrDeadline->diffForHumans() }} left to meet the seeding requirement
</div>

</div>

@endif


</div>

@else
<span class="text-danger">Torrent Deleted</span>
@endif

</strong>

@if(
    $type=='snatchlist' 
    && !$isSeeding 
    && $remainingSeed > 0 
    && !empty($history->completed_at)
    && $ratio < 1
)

<div class="seed-warning {{ $remainingSeed < 7200 ? 'urgent-hnr' : '' }}">
⚠ This torrent must be seeded to avoid Hit & Run
</div>

@endif

@if($type=='snatchlist' && !$hasMetRequirements)
<div class="mt-2">
    <form action="{{ route('bonus.buySeedtime') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="torrent_id" value="{{ $history->torrent_id }}">
        <button type="submit"
            class="btn btn-primary btn-sm"
            data-bs-toggle="tooltip"
            title="Buy seedtime ({{ config('seedbonus.shop.seedtime', 1000) }} seedbonus)">
            <i class="bi bi-coin"></i> Buy seedtime
        </button>
    </form>
</div>
@endif



{{-- SEED PROGRESS --}}
@if($type=='snatchlist' || $type=='need')

<div class="mt-3">

<div class="d-flex justify-content-between small mb-1">

<span>Seed Progress</span>

<span class="{{ $remainingSeed>0?'text-warning':'text-success' }}">

{{ $remainingSeed>0
? 'Left to seed: '.\App\Helpers\FormatHelper::formatTime($remainingSeed)
: 'Completed' }}

</span>

</div>

<div class="progress glass-progress">
<div class="progress-bar progress-glow"
style="width: {{ $progress }}%">
</div>
</div>

</div>

@endif

</div>



{{-- RATIO --}}
<div class="col-md-2 text-center">

<div class="ratio-circle">

<svg viewBox="0 0 36 36">

<path
d="M18 2.0845
a 15.9155 15.9155 0 0 1 0 31.831
a 15.9155 15.9155 0 0 1 0 -31.831"
fill="none"
stroke="#1f2937"
stroke-width="3"
/>

<path
stroke-dasharray="{{ $ratioPercent }},100"
d="M18 2.0845
a 15.9155 15.9155 0 0 1 0 31.831
a 15.9155 15.9155 0 0 1 0 -31.831"
fill="none"
stroke="{{ $type=='hnr' ? '#ef4444':'#22c55e' }}"
stroke-width="3"
/>

</svg>

<div class="ratio-text">
{{ $ratioDisplay }}
</div>

</div>

</div>



{{-- STATS --}}
<div class="col-md-4 text-md-end stats">

<span class="stat-pill upload"
data-bs-toggle="tooltip"
title="Actual Uploaded: {{ \App\Helpers\FormatHelper::formatSize($history->actual_uploaded ?? 0) }}">

<i class="bi bi-arrow-up"></i>
{{ \App\Helpers\FormatHelper::formatSize($history->uploaded) }}

</span>


<span class="stat-pill download"
data-bs-toggle="tooltip"
title="Actual Downloaded: {{ \App\Helpers\FormatHelper::formatSize($history->actual_downloaded ?? 0) }}">

<i class="bi bi-arrow-down"></i>
{{ \App\Helpers\FormatHelper::formatSize($history->downloaded) }}

</span>


<span class="stat-pill seed">

<i class="bi bi-clock"></i>
{{ \App\Helpers\FormatHelper::formatTime($history->seedtime ?? $history->total_seedtime ?? 0) }}

</span>

</div>

</div>

</div>