@extends('layouts.app')

@section('content')
    <div class="mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h3>Your Active Slots</h3>
                    </div>
                    <div class="card-body">
                        <p>Available Slots: <strong>{{ $user->slots }}</strong></p>

                        @if($slots->isEmpty())
                             <p class="text-center text-muted"><strong>No slots used yet.</strong></p>
                        @else
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Torrent</th>
                                        <th>Type</th>
                                        <th>Expires At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($slots as $slot)
                                    <tr>
                                        <td>{{ $slot->torrent->name ?? 'Deleted Torrent' }}</td>
                                        <td>
                                            @if($slot->free) 
                                                <span class="badge bg-success">Free Download</span>
                                            @endif
                                            @if($slot->double) 
                                                <span class="badge bg-info">Double Upload</span>
                                            @endif
                                        </td>
                                        <td>{{ $slot->expires_at }}</td>
                                        <td>
                                            @if($slot->expires_at->isPast()) <!-- Show buttons only if expired -->
                                                <!-- Renew Button -->
                                                <form action="{{ route('slots.renew', $slot->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Renewing the slot will decrease your available slots">Renew</button>
                                                </form>

                                                <!-- Remove Button -->
                                                <form action="{{ route('slots.remove', $slot->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Removing the slot means you will have to use another slot to make this torrent free or double upload">Remove</button>
                                                </form>
                                            @else
                                                <span class="text-muted">Active Slot </span>
                                            @endif

                                            <!-- Download Button (only visible if the slot is active) -->
                                            @if(!$slot->expires_at->isPast())
                                                <form action="{{ route('torrents.download', ['id' => $slot->torrent->id, 'slug' => $slot->torrent->slug]) }}" method="GET" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="{{ $slot->torrent->name }}"><i class="bi bi-file-earmark-arrow-down-fill"></i> Download</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
