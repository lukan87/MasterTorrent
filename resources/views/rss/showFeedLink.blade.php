@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Generated RSS Feed Link</h1>

        <p>Your RSS feed has been generated successfully. You can view it by clicking the link below:</p>

        <div>
            <a href="{{ $rssFeedUrl }}" target="_blank" class="btn btn-primary">View RSS Feed</a>

           <p> Your Feed -  {{ $rssFeedUrl }}</p>
                </div>

        <hr>

        <h2>Feed Information</h2>


        <h3>Most Recent Torrents:</h3>
        <ul>
            @foreach ($torrents as $torrent)
                <li>{{ $torrent->name }} ({{ $torrent->category->name }})</li>
            @endforeach
        </ul>
    </div>
@endsection\
