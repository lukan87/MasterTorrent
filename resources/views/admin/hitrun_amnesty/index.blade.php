@extends('layouts.app')

@section('content')

<div class="container-fluid px-3 px-md-4 py-3">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1" style="color:#f8fafc"><i class="bi bi-tools"></i> Hit &amp; Run Amnesty</h2>
            <p class="mb-0 text-muted small">Give every user a 1:1 ratio on each affected torrent and clear all hit &amp; runs.</p>
        </div>
        <div class="badge bg-danger"><i class="bi bi-shield-exclamation"></i> Administrative Action</div>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    {{-- Preview status --}}
    <div class="card mb-4" style="background:#1e1e2f;border:1px solid rgba(148,163,184,.16)">
        <div class="card-body">
            <h5 class="fw-bold mb-3" style="color:#67e8df"><i class="bi bi-eye"></i> Current Preview</h5>
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="border rounded p-3 text-center" style="background:#0b0b16">
                        <div class="small text-muted">Hit &amp; Runs</div>
                        <div class="fs-4 fw-bold">{{ number_format($stats['records']) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded p-3 text-center" style="background:#0b0b16">
                        <div class="small text-muted">Affected Users</div>
                        <div class="fs-4 fw-bold">{{ number_format($stats['users']) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded p-3 text-center" style="background:#0b0b16">
                        <div class="small text-muted">Upload to Credit (1:1)</div>
                        <div class="fs-4 fw-bold">{{ \App\Helpers\FormatHelper::formatSize($stats['total_upload_credited']) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded p-3 text-center" style="background:#0b0b16">
                        <div class="small text-muted">Warnings / DL Locks</div>
                        <div class="fs-4 fw-bold">{{ number_format($stats['warnings_cleared']) }} / {{ number_format($stats['downloads_restored']) }}</div>
                    </div>
                </div>
            </div>

            @if($stats['records'] === 0)
                <div class="alert alert-success mt-3 mb-0 fw-bold">&#127881; There are no hit & runs to clear.</div>
            @else
                <div class="mt-4">
                    <h5 class="fw-bold mb-3" style="color:#67e8df"><i class="bi bi-people"></i> Affected Users</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Hit & Run Torrents</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['affected_users'] as $uid => $data)
                                    <tr>
                                        <td>{{ $data['user'] }}</td>
                                        <td>{{ implode(', ', $data['torrents']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($stats['records'] > 0)
        {{-- Confirm form --}}
        <div class="card" style="background:#1e1e2f;border:1px solid rgba(239,68,68,.35)">
            <div class="card-body">
                <h5 class="fw-bold mb-2 text-danger"><i class="bi bi-exclamation-triangle"></i> Apply Amnesty</h5>
                <p class="text-muted small mb-3">This adds upload to affected users to give a 1:1 ratio on each hit &amp; run torrent, then clears the hit &amp; runs, resets counters and restores any H&amp;R-triggered restrictions. A summary is sent to each affected user. This action cannot be undone.</p>

                <form method="POST" action="{{ route('admin.hitrun_amnesty.run') }}">
                    @csrf
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirm" value="1" id="confirm" required>
                        <label class="form-check-label" for="confirm">
                            I understand this is permanent and want to clear all {{ number_format($stats['records']) }} hit &amp; runs now.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="bi bi-trash3"></i> Clear All Hit &amp; Runs</button>
                </form>
            </div>
        </div>
    @endif

</div>

@endsection
