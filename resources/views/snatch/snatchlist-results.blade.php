
@include('snatch.partials.page', [
    'records' => $snatchlist,
    'section' => 'snatchlist',
    'title' => 'Snatch history',
    'description' => $search !== '' ? 'Search results from the full snatch history, including older entries with requirements met.' : 'Entries from the last 10 days, plus older torrents until the seed time or ratio requirement is met.',
    'emptyMessage' => $search !== '' ? 'No torrents match your search.' : 'No recent torrents or outstanding seeding requirements.',
])
