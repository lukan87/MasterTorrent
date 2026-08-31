@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Log Details</h1>

    <div class="card">
        <div class="card-body">

            <p><strong>ID:</strong> {{ $log->id }}</p>

            <p><strong>User:</strong> 
                {{ $log->user->name ?? 'Unknown User' }}
            </p>

            <p><strong>Torrent:</strong> 
                {{ $log->torrent->name ?? 'Deleted Torrent' }}
            </p>

            <p><strong>Action:</strong> 
                <span class="badge bg-{{ 
                    $log->action === 'uploaded' ? 'success' : 
                    ($log->action === 'edited' ? 'warning' : 
                    ($log->action === 'deleted' ? 'danger' : 'secondary')) 
                }}">
                    {{ ucfirst($log->action) }}
                </span>
            </p>

<p><strong>Changes:</strong></p>

@php
$changes = [];

if (!empty($log->description)) {

    $parts = explode(',', $log->description);

    foreach ($parts as $part) {

        $part = trim($part);

        if (!str_contains($part, ':') || !str_contains($part, '→')) {
            continue;
        }

        $fieldSplit = explode(':', $part, 2);

        if (!isset($fieldSplit[0], $fieldSplit[1])) {
            continue;
        }

        $valueSplit = explode('→', $fieldSplit[1], 2);

        if (!isset($valueSplit[0], $valueSplit[1])) {
            continue;
        }

        $changes[] = [
            'field' => trim($fieldSplit[0]),
            'old'   => trim($valueSplit[0], " '"),
            'new'   => trim($valueSplit[1], " '"),
        ];
    }
}
@endphp


@if(count($changes))
<div class="mb-4">

    @foreach($changes as $change)

        @php
            $old = $change['old'] === '1' ? 'Yes' : ($change['old'] === '0' ? 'No' : $change['old']);
            $new = $change['new'] === '1' ? 'Yes' : ($change['new'] === '0' ? 'No' : $change['new']);
            $field = str_replace('_', ' ', $change['field']);
        @endphp

        <div class="border rounded p-3 mb-3">

            {{-- FIELD --}}
            <div class="fw-semibold text-uppercase small text-muted mb-2">
                {{ $field }}
            </div>

            {{-- BEFORE / AFTER --}}
            <div class="row g-2 align-items-center">

                <div class="col-12 col-md-5">
                    <div class="small text-muted">Before</div>
                    <div class="p-2 border rounded">
                        {!! convertCustomTagsToHtml($old) !!}
                    </div>
                </div>

                <div class="col-12 col-md-2 text-center">
                    <i class="bi bi-arrow-right fs-5 text-muted"></i>
                </div>

                <div class="col-12 col-md-5">
                    <div class="small text-muted">After</div>
                    <div class="p-2 border rounded">
                        {!! convertCustomTagsToHtml($new) !!}
                    </div>
                </div>

            </div>

        </div>

    @endforeach

</div>

@else
    <div class="border p-3 mb-3 text-muted">
        {{ $log->description ?? 'No description provided' }}
    </div>
@endif

            <p><strong>Date:</strong> 
                {{ $log->created_at->format('Y-m-d H:i:s') }}
            </p>

            <a href="{{ route('admin.torrent_logs.index') }}" class="btn btn-secondary">
                Back to Logs
            </a>

        </div>
    </div>
</div>
@endsection
