@if($poster = $request->safeUrl($request->image))
    <img class="rq-poster" src="{{ $poster }}" alt="Poster for {{ $request->name }}" loading="lazy" referrerpolicy="no-referrer" onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
    <div class="rq-poster rq-placeholder" hidden aria-hidden="true"><i class="bi bi-film"></i></div>
@else
    <div class="rq-poster rq-placeholder" aria-hidden="true"><i class="bi bi-film"></i></div>
@endif
