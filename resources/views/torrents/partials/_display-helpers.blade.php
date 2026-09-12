{{-- =========================================================
    Display helpers (rating badges + language map)

    Shared so the premium media-header can be reused outside of
    the torrent show page (e.g. library.movies.show / library.series.show).
    All definitions are guarded to avoid redeclaration when the torrent
    partials (tv.blade.php / movie.blade.php) already define them.
========================================================== --}}

@php

if (! function_exists('getRatingBadge')) {
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
                    <i class='bi bi-question-circle'></i> " . $rating . "
                </span>";
        }
    }
}

if (! function_exists('getTVRatingBadge')) {
    function getTVRatingBadge($rating) {
        $ratings = [
            'TV-Y'   => ['class' => 'tv-y-rating',   'icon' => 'bi-balloon-heart',     'title' => 'TV-Y — All Children.'],
            'TV-Y7'  => ['class' => 'tv-y7-rating',  'icon' => 'bi-emoji-sunglasses',  'title' => 'TV-Y7 — Older Children.'],
            'TV-G'   => ['class' => 'tv-g-rating',   'icon' => 'bi-people',            'title' => 'TV-G — General Audience.'],
            'TV-PG'  => ['class' => 'tv-pg-rating',  'icon' => 'bi-exclamation-circle','title' => 'TV-PG — Parental Guidance Suggested.'],
            'TV-14'  => ['class' => 'tv-14-rating',  'icon' => 'bi-shield-exclamation','title' => 'TV-14 — Parents Strongly Cautioned.'],
            'TV-MA'  => ['class' => 'tv-ma-rating',  'icon' => 'bi-explicit',          'title' => 'TV-MA — Mature Audience Only.'],
        ];

        $rating = strtoupper($rating);

        if (isset($ratings[$rating])) {
            $r = $ratings[$rating];
            return "<span class='rating-badge {$r['class']}' data-bs-toggle='tooltip' title='{$r['title']}'>
                        <i class='bi {$r['icon']}'></i> {$rating}
                    </span>";
        }

        return "<span class='rating-badge'>
                    <i class='bi bi-question-circle'></i> {$rating}
                </span>";
    }
}

if (! function_exists('getLanguageName')) {
    function getLanguageName($code) {
        $languages = [
            'en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German',
            'it' => 'Italian', 'ja' => 'Japanese', 'ko' => 'Korean', 'zh' => 'Chinese',
            'ru' => 'Russian', 'hi' => 'Hindi',
        ];
        return $languages[strtolower($code)] ?? strtoupper($code);
    }
}

@endphp