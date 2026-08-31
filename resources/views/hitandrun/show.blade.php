@extends('layouts.app')

@section('content')

<h1>{{ $user->name }}'s Hit and Run Status</h1>

@if($torrentsToSeed->isEmpty())
    <p>{{ $user->name }} doesn't have any torrents that need seeding.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Torrent Name</th>
                <th>Seeding Time Left</th>
            </tr>
        </thead>
        <tbody>
            @foreach($torrentsToSeed as $torrent)
                <tr>
                    <td>{{ $torrent->torrent_name }}</td>
                    <td>{{ $torrent->seeding_time_left }} hours</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif


@endsection
