
@include('snatch.partials.page', [
    'records' => $needToSeed,
    'section' => 'need',
    'title' => 'Needs seeding',
    'description' => 'Unmet seeding obligations that need attention.',
    'emptyMessage' => 'No torrents need seeding.',
])
