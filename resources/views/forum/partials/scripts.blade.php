@once
<script type="application/json" id="forum-config">{!! json_encode([
    'user' => auth()->id(),
    'preview' => route('forum.preview'),
    'saved' => session('forum_draft_saved'),
    'draft' => $draftKind.':'.$draftId,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('js/forum.js') }}?v={{ filemtime(public_path('js/forum.js')) }}" defer></script>
@endonce
