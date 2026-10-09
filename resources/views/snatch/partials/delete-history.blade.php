@if(auth()->check() && auth()->user()->user_class >= \App\Models\UserClass::ADMIN
    && !((int) $history->user_id === (int) auth()->id()
        && (int) ($torrent->owner ?? $history->torrent?->owner) === (int) auth()->id()))
<form action="{{ route('snatch.deleteHistory', ['historyId' => $history->id]) }}"
      method="POST" class="snatch-delete-form"
      onsubmit="return confirm('Delete this snatch from history? This cannot be undone.');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" title="Remove from history">
        <i class="bi bi-trash"></i> Delete snatch
    </button>
</form>
@endif
