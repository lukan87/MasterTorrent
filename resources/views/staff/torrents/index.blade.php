@extends('layouts.app')

@section('content')

<div class="container">

<h2>Torrent Moderation</h2>

<table class="table table-striped">

<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Uploader</th>
<th>Seeders</th>
<th>Leechers</th>
<th>Created</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

@foreach($torrents as $torrent)

<tr>
<td>{{ $torrent->id }}</td>

<td>
<a href="{{ route('torrents.show', $torrent->id) }}">
{{ $torrent->name }}
</a>
</td>

<td>
{{ $torrent->uploader ? $torrent->uploader->name : 'Unknown' }}
</td>

<td>{{ $torrent->seeders }}</td>

<td>{{ $torrent->leechers }}</td>

<td>{{ $torrent->created_at }}</td>

<td>

<a href="/staff/torrents/{{ $torrent->id }}" class="btn btn-sm btn-primary">
View
</a>

<form action="{{ route('staff.torrents.destroy', $torrent->id) }}" method="POST" style="display:inline;">
@csrf
@method('DELETE')

<button class="btn btn-sm btn-danger"
onclick="return confirm('Delete this torrent?')">

Delete

</button>

</form>

</td>

</tr>

@endforeach

</tbody>

</table>

{{ $torrents->links() }}

</div>

@endsection