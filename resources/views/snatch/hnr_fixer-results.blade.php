
@include('snatch.partials.page', [
    'records' => $hnrFixer,
    'section' => 'fixer',
    'title' => 'Hit & Run fixer',
    'description' => 'Review flagged activity and choose how to resolve it.',
    'emptyMessage' => 'No Hit & Run records to resolve.',
])
