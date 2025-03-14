@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Warnings for <strong>{{ $user->name }}</strong></h2>

    <!-- Active Warnings Section -->
    <h4 class="mb-3">Active Warnings ({{ $warningcount }})</h4>
    @if($warnings->count())
        <div class="card">
            <div class="card-header">
                Active Warnings
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Warning ID</th>
                                <th>Torrent</th>
                                <th>Issued By</th>
                                <th>Status</th>
                               <th>Expires On: </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($warnings as $warning)
                            <tr>
                                <td>{{ $warning->id }}</td>
                                <td>{{ $warning->torrenttitle->name ?? 'N/A' }}</td>
                                <td>{{ $warning->warnedBy->name ?? 'Unknown' }}</td>
                                <td>{{ $warning->active ? 'Active' : 'Inactive' }}</td>
                                <td>
    @if ($warning->expires_on && \Carbon\Carbon::parse($warning->expires_on)->isPast())
        <span class="text-danger">Warning Expired</span>
    @else
        {{ $warning->expires_on }}
    @endif
</td>

                               
                                
                                
                                <td>
                                    <form action="{{ route('warnings.deactivate', $warning->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-warning btn-sm">Deactivate</button>
                                    </form>
                                    <form action="{{ route('warnings.delete', $warning->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $warnings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @else
        <p class="text-muted">No active warnings found.</p>
    @endif

    <!-- Soft Deleted Warnings Section -->
    <h4 class="mt-5 mb-3">Deleted Warnings ({{ $softDeletedWarningCount }})</h4>
    @if($softDeletedWarnings->count())
        <div class="card">
            <div class="card-header">
                Deleted Warnings
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Warning ID</th>
                                <th>Torrent</th>
                                <th>Issued By</th>
                                <th>Deleted By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($softDeletedWarnings as $warning)
                            <tr>
                                <td>{{ $warning->id }}</td>
                                <td>{{ $warning->torrenttitle->name ?? 'N/A' }}</td>
                                <td>{{ $warning->warnedBy->name ?? 'Unknown' }}</td>
                                <td><b>{{ $warning->deletedBy->name ?? 'Unknown' }}</b></td> <!-- Use deletedBy relationship here -->
                                <td>
                                    <form action="{{ route('warnings.restore', $warning->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Restore</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $softDeletedWarnings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @else
        <p class="text-muted">No deleted warnings found.</p>
    @endif

    <!-- Deactivate All Warnings Form -->
    <div class="mt-4">
        <form action="{{ route('warnings.deactivateAll', ['id' => $user->id, 'username' => $user->username]) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-warning">Deactivate All Warnings</button>
        </form>
        
        <!-- Delete All Warnings Form -->
        <form action="{{ route('warnings.deleteAll', ['id' => $user->id, 'username' => $user->username]) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-danger">Delete All Warnings</button>
        </form>
    </div>
</div>
@endsection
