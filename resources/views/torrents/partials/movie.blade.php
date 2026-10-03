@include('torrents.partials.css.media-css')
@include('torrents.partials.backdrop-slideshow')
@include('torrents.partials.media-header', [
    'torrent' => $torrent,
    'display' => $display
])



<!-- CAST -->




@includeWhen(
    $display['type'] === 'movie',
    'torrents.partials.collection'
)



@php
function getRatingBadge($rating) {
    switch (strtoupper($rating)) {
        case 'G':
            return "<span class='rating-badge g-rating' data-bs-toggle='tooltip'
                title='G — General Audiences. Suitable for all ages; contains no offensive material or content.'>
                <i class='bi bi-emoji-smile'></i> G
            </span>";

        case 'PG':
            return "<span class='rating-badge pg-rating' data-bs-toggle='tooltip'
                title='PG — Parental Guidance Suggested. Some material may not be suitable for children (e.g. mild language or brief peril).'>
                <i class='bi bi-emoji-neutral'></i> PG
            </span>";

        case 'PG-13':
            return "<span class='rating-badge pg13-rating' data-bs-toggle='tooltip'
                title='PG-13 — Parents Strongly Cautioned. Some material may be inappropriate for children under 13 due to violence, language, or mature themes.'>
                <i class='bi bi-emoji-frown'></i> PG-13
            </span>";

        case 'R':
            return "<span class='rating-badge r-rating' data-bs-toggle='tooltip'
                title='R — Restricted. Under 17 requires accompanying parent or adult guardian. Contains strong language, violence, or adult themes.'>
                <i class='bi bi-shield-exclamation'></i> R
            </span>";

        case 'NC-17':
            return "<span class='rating-badge nc17-rating' data-bs-toggle='tooltip'
                title='NC-17 — Adults Only. No one 17 and under admitted. May contain explicit sexual content or graphic violence.'>
                <i class='bi bi-explicit'></i> NC-17
            </span>";

        default:
            return "<span class='rating-badge unrated-rating' data-bs-toggle='tooltip'
                title='Unrated or not classified by MPAA.'>
                <i class='bi bi-question-circle'></i> $rating
            </span>";
    }
}

function getLanguageName($code) {
    $languages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'ru' => 'Russian',
        'hi' => 'Hindi'
    ];
    
    return $languages[$code] ?? strtoupper($code);
}
@endphp
