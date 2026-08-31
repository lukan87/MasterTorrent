@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Polls</h1>

        @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
            <a href="{{ route('polls.create') }}" class="btn btn-success">
                New Poll
            </a>
        @endif
    </div>

    {{-- Flash messages --}}
    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }} shadow-sm">
                {{ session($key) }}
            </div>
        @endif
    @endforeach

    @if($polls->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <h5>No polls available</h5>
            </div>
        </div>
    @else

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Poll</th>
                        <th>Status</th>
                        <th class="text-center">Votes</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($polls as $poll)

                    @php
                        $isAdmin   = Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN;
                        $isDeleted = $poll->trashed();
                    @endphp

                    <tr class="{{ $isDeleted ? 'opacity-75' : '' }}">

                        {{-- Poll info --}}
                        <td>
                            <div class="fw-semibold">{{ $poll->title }}</div>
                            <small class="text-muted">
                                {{ \Illuminate\Support\Str::limit($poll->description, 60) }}
                            </small>

                            @if($isDeleted)
                                <span class="badge bg-secondary ms-2">Deleted</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            @if($isDeleted)
                                <span class="badge bg-secondary">Archived</span>
                            @elseif($poll->isExpired())
                                <span class="badge bg-warning text-dark">Expired</span>
                            @elseif(!$poll->is_active)
                                <span class="badge bg-light text-dark border">Closed</span>
                            @else
                                <span class="badge bg-success bg-opacity-75">Open</span>
                            @endif
                        </td>

                        {{-- Votes --}}
                        <td class="text-center fw-semibold text-muted">
                            {{ $poll->votes_count }}
                        </td>

                        {{-- Actions --}}
                        <td class="text-end">

                            <a href="{{ route('polls.show', $poll->id) }}"
                               class="btn btn-sm btn-outline-primary me-1">
                                View
                            </a>

                            @if($isAdmin)

                                <a href="{{ route('polls.edit', $poll->id) }}"
                                   class="btn btn-sm btn-outline-secondary me-1">
                                    Edit
                                </a>

                                {{-- Toggle --}}
                                <form action="{{ route('polls.toggle', $poll->id) }}"
                                      method="POST"
                                      class="d-inline me-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm {{ $poll->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                        {{ $poll->is_active ? 'Close' : 'Open' }}
                                    </button>
                                </form>

                                @if($isDeleted)
                                    {{-- Restore --}}
                                    <form action="{{ route('polls.restore', $poll->id) }}"
                                          method="POST"
                                          class="d-inline me-1"
                                          onsubmit="return confirm('Restore this poll?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            Restore
                                        </button>
                                    </form>
                                @else
                                    {{-- Soft delete --}}
                                    <form action="{{ route('polls.destroy', $poll->id) }}"
                                          method="POST"
                                          class="d-inline me-1"
                                          onsubmit="return confirm('Archive this poll?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Archive
                                        </button>
                                    </form>
                                @endif

                                {{-- Force delete --}}
                                <form action="{{ route('polls.force-delete', $poll->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('PERMANENTLY delete this poll?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-dark">
                                        Delete
                                    </button>
                                </form>

                            @endif
                        </td>
                    </tr>

                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
