
@include('snatch.partials.page', [
    'records' => $seeding,
    'section' => 'seeding',
    'title' => 'Currently seeding',
    'description' => 'Active seed sessions helping keep torrents available.',
    'emptyMessage' => 'No active seeding sessions.',
])
