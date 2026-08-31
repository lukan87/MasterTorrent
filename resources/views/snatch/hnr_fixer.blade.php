@extends('layouts.app')

@section('content')

<div class="container my-5" style="background:#1e1e2f;padding:2rem;border-radius:0.5rem">

<h2 class="text-center text-danger mb-4">
<i class="bi bi-tools"></i> Hit & Run Fixer
</h2>

@if($hnrFixer->isEmpty())

<div class="alert alert-success text-center fw-bold">
🎉 You have no Hit & Runs!
</div>

@else

@foreach($hnrFixer as $history)

@php

$ratio = $history->downloaded > 0
? $history->uploaded / $history->downloaded
: 0;

$remainingSeed = max(0, $requiredSeedtime - $history->seedtime);

$status = $history->active ? 'Seeding' : 'Offline';

@endphp


<div class="card mb-3 border-0 hnr-card">

<div class="card-body d-flex justify-content-between align-items-center flex-wrap">


<div>

<strong>

<a href="{{ route('torrents.show',['id'=>$history->torrent->id]) }}"
class="text-white text-decoration-none">

{{ $history->torrent->name }}

</a>

</strong>

<div class="text-muted small">

Remaining Seed:

<span class="text-warning">

{{ $remainingSeed > 0
? \App\Helpers\FormatHelper::formatTime($remainingSeed)
: 'Completed' }}

</span>

</div>

</div>


<div>

<span class="badge bg-secondary">

Ratio: {{ number_format($ratio,2) }}

</span>

<span class="badge {{ $history->active ? 'bg-success':'bg-danger' }}">

{{ $status }}

</span>

</div>


<div>

<a href="{{ route('torrents.download',[
'id'=>$history->torrent->id,
'slug'=>$history->torrent->slug
]) }}"
class="btn btn-warning btn-sm">

<i class="bi bi-download"></i> Resume Seeding

</a>

</div>


</div>
</div>

@endforeach


<div class="d-flex justify-content-center mt-4">
{{ $hnrFixer->links('pagination::bootstrap-5') }}
</div>

@endif

</div>


<style>

.hnr-card{
background:linear-gradient(90deg,rgba(220,53,69,0.1)0%,rgba(255,255,255,0)100%);
border-left:6px solid #dc3545;
transition:transform .3s,box-shadow .3s;
}

.hnr-card:hover{
transform:translateY(-4px);
box-shadow:0 12px 25px rgba(0,0,0,.5);
}

</style>

@endsection