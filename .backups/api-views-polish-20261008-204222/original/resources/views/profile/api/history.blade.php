@extends('layouts.app')
@section('title','Upload History')
@section('content')
<div class="mx-auto my-4" style="max-width:1080px">
    <a href="{{ route('profile.api.index') }}" class="btn btn-outline-info btn-sm mb-3">← API &amp; automation</a>
    <h1 class="h3">{{ $admin ? 'Upload audit history' : 'Your upload history' }}</h1>
    <p class="text-muted">Website, API and seedbox uploads recorded since upload monitoring was enabled.</p>
    @if($admin)
    <form method="get" class="d-flex flex-wrap gap-2 mb-3"><label class="visually-hidden" for="audit-method">Upload method</label><select class="form-select w-auto" id="audit-method" name="method"><option value="">All methods</option>@foreach(['website','api','seedbox'] as $method)<option value="{{ $method }}" @selected(request('method')===$method)>{{ ucfirst($method) }}</option>@endforeach</select>
    <label class="visually-hidden" for="audit-status">Status</label><select class="form-select w-auto" id="audit-status" name="status"><option value="">All statuses</option>@foreach(['completed','failed','duplicate','processing'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="btn btn-outline-info" type="submit">Filter</button></form>
    @elseif(auth()->user()->user_class >= \App\Models\UserClass::ADMIN)<a href="{{ route('admin.upload-history') }}" class="btn btn-outline-secondary btn-sm mb-3">View administrator audit</a>@endif
    <div class="card"><div class="card-body p-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Torrent</th><th>Date (UTC)</th><th>Method / token</th><th>Status</th>@if($admin)<th>Account</th>@endif</tr></thead><tbody>
    @forelse($attempts as $attempt)<tr><td>@if($attempt->torrent)<a href="{{ route('torrents.show',['id'=>$attempt->torrent->id,'slug'=>$attempt->torrent->slug]) }}">{{ $attempt->torrent_name }}</a>@else{{ $attempt->torrent_name ?? 'Upload attempt #'.$attempt->id }}@endif</td>
    <td class="small">{{ $attempt->created_at->utc()->format('d M Y H:i') }}</td><td>{{ ucfirst($attempt->method) }}@if($attempt->token_name)<div class="small text-muted">{{ $attempt->token_name }}</div>@endif</td><td>{{ ucfirst($attempt->status) }}@if($attempt->errorMessage())<div class="small text-muted">{{ $attempt->errorMessage() }}</div>@endif</td>@if($admin)<td>{{ $attempt->user?->name ?? 'Deleted account' }}</td>@endif</tr>
    @empty<tr><td colspan="{{ $admin ? 5 : 4 }}" class="text-muted text-center py-4">No upload attempts yet. Your first upload will appear here.</td></tr>@endforelse
    </tbody></table></div>{{ $attempts->links() }}</div></div>
</div>
@endsection
