@if($paginator->total() > 0)
<nav class="tx-pagination tx-pagination-{{ $position ?? 'bottom' }}" aria-label="Torrent pagination {{ $position ?? 'bottom' }}">
    <div class="tx-pagination-summary">
        <span class="tx-pagination-icon" aria-hidden="true"><i class="bi bi-collection-play"></i></span>
        <div>
            <span class="tx-pagination-page">Page <strong>{{ number_format($paginator->currentPage()) }}</strong> of {{ number_format($paginator->lastPage()) }}</span>
            <span class="tx-pagination-range">Showing {{ number_format($paginator->firstItem() ?? 0) }}–{{ number_format($paginator->lastItem() ?? 0) }} of <strong>{{ number_format($paginator->total()) }}</strong> torrents</span>
        </div>
    </div>
    <div class="tx-pagination-controls">
        @if($paginator->onFirstPage())
            <span class="tx-page-link tx-page-direction is-disabled" aria-disabled="true" aria-label="Previous page"><i class="bi bi-chevron-left" aria-hidden="true"></i><span>Previous</span></span>
        @else
            <a class="tx-page-link tx-page-direction" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><i class="bi bi-chevron-left" aria-hidden="true"></i><span>Previous</span></a>
        @endif
        <ul class="tx-page-numbers">
            @foreach($elements as $element)
                @if(is_string($element))
                    <li><span class="tx-page-gap" aria-hidden="true">{{ $element }}</span></li>
                @else
                    @foreach($element as $page => $url)
                        <li>
                            @if($page === $paginator->currentPage())
                                <span class="tx-page-link is-current" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                            @else
                                <a class="tx-page-link" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>
        <span class="tx-page-mobile" aria-hidden="true">{{ $paginator->currentPage() }} <span>/</span> {{ $paginator->lastPage() }}</span>
        @if($paginator->hasMorePages())
            <a class="tx-page-link tx-page-direction" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><span>Next</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        @else
            <span class="tx-page-link tx-page-direction is-disabled" aria-disabled="true" aria-label="Next page"><span>Next</span><i class="bi bi-chevron-right" aria-hidden="true"></i></span>
        @endif
    </div>
</nav>
@endif
