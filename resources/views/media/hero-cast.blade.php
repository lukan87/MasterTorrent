@if(!empty($cast))
<section class="md-cast" data-media-cast aria-label="Featured cast">
    <div class="md-cast-header">
        <h2><i class="bi bi-people" aria-hidden="true"></i>Featured cast</h2>
        <div class="md-cast-controls" hidden>
            <button type="button" data-media-cast-step="-1" aria-label="Previous cast members" aria-controls="detail-cast-{{ $mediaType }}-{{ $media->id }}" disabled><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
            <button type="button" data-media-cast-step="1" aria-label="Next cast members" aria-controls="detail-cast-{{ $mediaType }}-{{ $media->id }}" disabled><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="md-cast-track" id="detail-cast-{{ $mediaType }}-{{ $media->id }}" tabindex="0" role="region" aria-label="Cast members; scroll for more">
        @foreach($cast as $actor)
            @php
                $name = $actor['name'] ?? 'Unknown actor';
                $character = $actor['character'] ?? '';
                $photo = !empty($actor['profile_path']) ? 'https://image.tmdb.org/t/p/w185'.$actor['profile_path'] : asset('images/not-found.jpg');
            @endphp
            <div class="md-cast-person">
                @if(!empty($actor['id']))<a href="{{ route('actors.show', $actor['id']) }}">@else<div>@endif
                    <img class="md-image" src="{{ $photo }}" data-fallback="{{ asset('images/not-found.jpg') }}" alt="{{ $name }}" loading="lazy" width="76" height="76" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-container="body" title="{{ $character ?: $name }}">
                    <span class="md-cast-name">{{ $name }}</span>
                    @if($character)<span class="visually-hidden">as {{ $character }}</span>@endif
                @if(!empty($actor['id']))</a>@else</div>@endif
            </div>
        @endforeach
    </div>
</section>
@endif
