
@include('snatch.partials.page', [
    'records' => $leeching,
    'section' => 'leeching',
    'title' => 'Currently downloading',
    'description' => 'Active downloads and progress toward completion.',
    'emptyMessage' => 'No active downloads.',
])
