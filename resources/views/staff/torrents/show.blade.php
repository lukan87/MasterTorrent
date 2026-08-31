@extends('layouts.app')

@section('content')

<div class="container">

<h2>{{ $torrent->name }}</h2>

<p>
<strong>Uploader:</strong>
{{ $torrent->uploader->name ?? 'Unknown' }}
</p>

<p>
<strong>Category:</strong>
{{ $torrent->category->name ?? '-' }}
</p>

<p>
<strong>Size:</strong>
{{ number_format($torrent->size) }} bytes
</p>

<p>
<strong>Seeders:</strong>
{{ $torrent->seeders }}
</p>

<p>
<strong>Leechers:</strong>
{{ $torrent->leechers }}
</p>

<hr>

<h4>Files</h4>

<ul>
@foreach($torrent->files as $file)
<li>{{ $file->name }} ({{ $file->size }})</li>
@endforeach
</ul>

</div>

@endsection