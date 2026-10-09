
@include('snatch.partials.page', [
    'records' => $hitAndRun,
    'section' => 'hnr',
    'title' => 'Hit & Run',
    'description' => 'Flagged torrents: resume seeding or use bonus points to clear an H&R.',
    'emptyMessage' => 'No Hit & Run records.',
])
