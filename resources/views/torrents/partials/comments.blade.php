<div class="card card-blur mt-4">
    <div class="card-header">
        <h5>Comments for {{ $torrent->name }}</h5>
    </div>
    <div class="card-body">
        <!-- Comment Form -->
        <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
            @csrf
            <input type="hidden" name="commentable_id" value="{{ $torrent->id }}">
            <input type="hidden" name="commentable_type" value="torrent">
            <input type="hidden" name="torrent_id" value="{{ $torrent->id }}">
            <div class="mb-3">
                <div class="mb-2 d-flex flex-wrap gap-2">
                    <!-- Font Size Dropdown -->
                    <select id="fontSize" class="form-select form-select-sm d-inline-block" style="width: auto;">
                        <option value="14">1 (Small)</option>
                        <option value="16">2 (Normal)</option>
                        <option value="18">3 (Medium)</option>
                        <option value="20">4 (Large)</option>
                        <option value="22">5 (Extra Large)</option>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('size', document.getElementById('fontSize').value)">Use Size</button>
            
                    <!-- Font Color Dropdown -->
                    <select id="fontColor" class="form-select form-select-sm d-inline-block" style="width: auto;">
                        <option value="black">Black</option>
                        <option value="red">Red</option>
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="purple">Purple</option>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('color', document.getElementById('fontColor').value)">Use Color</button>
            
                    <!-- Font Family Dropdown -->
                    <select id="fontFamily" class="form-select form-select-sm d-inline-block" style="width: auto;">
                        <option value="Arial">Arial</option>
                        <option value="Verdana">Verdana</option>
                        <option value="Courier">Courier</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Times New Roman">Times New Roman</option>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('font', document.getElementById('fontFamily').value)">Use Font Family</button>
            
                    <!-- Center Button -->
                   
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('center')">Center</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('b')">Bold</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('i')">Italic</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('u')">Underline</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('quote')">Quote</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('youtube')">YouTube</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('img')">Image</button>
                   
                </div>
                <textarea id="comment" name="comment" class="form-control" rows="6" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Comment</button>
        </form>
        <hr>

        @if($comments->isEmpty())
            <p>No comments yet</p>
        @else
            @foreach($comments as $comment)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div class="comment-details">
                            <h5 class="card-subtitle mb-2 text-muted">
                                {{ $comment->user->name ?? 'Unknown' }} <b>@ {{ $comment->created_at }}</b>
                            </h5>
                            <p class="card-text" id="comment-text-{{ $comment->id }}">
                                {!! convertCustomTagsToHtml( $comment->comment ) !!}
                            </p>
                        </div>

                        <div class="comment-actions d-flex flex-column justify-content-start">
                            @if (Auth::user()->user_class > 5 || $comment->user_id == Auth::id())
                                <button class="btn btn-warning btn-sm mb-2" data-bs-toggle="collapse" data-bs-target="#edit-comment-{{ $comment->id }}">
                                    <i class="bi bi-pencil" data-bs-toggle="tooltip" title="Edit Comment"></i>
                                </button>

                                <form action="{{ route('comments.destroy', $comment->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete Comment">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div id="edit-comment-{{ $comment->id }}" class="collapse">
                        <form action="{{ route('comments.update', ['id' => $comment->id]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3 mt-3">
                                <textarea name="comment" class="form-control" rows="3" required>{{ $comment->comment }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </form>
                    </div>
                </div>
            @endforeach
            {{ $comments->links() }} <!-- Pagination links -->
        @endif
    </div>
</div>

<!-- Custom Styling -->
<style>
    .collapse {
        margin-top: 10px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: rgb(67, 68, 68);
    }

    .comment-actions button {
        width: 100%;
        text-align: center;
    }

    .comment-details {
        flex: 1;
    }

    .btn-warning, .btn-danger, .btn-primary {
        font-size: 14px;
        padding: 6px 12px;
    }
</style>

<script>
    

    function insertBBCode(tag, option = null) {
    const textarea = document.getElementById("comment"); // Make sure this matches your textarea ID
    const startTag = option ? `[${tag}=${option}]` : `[${tag}]`;
    const endTag = `[/${tag}]`;
    const cursorPosition = textarea.selectionStart; // Store current cursor position
    const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);

    // Insert the BBCode tags with the selected text in the middle
    const newText = startTag + selectedText + endTag;
    textarea.value = textarea.value.substring(0, cursorPosition) + newText + textarea.value.substring(textarea.selectionEnd);

    // Set the cursor position in the middle of the tags, right after the opening tag
    const newCursorPosition = cursorPosition + startTag.length;
    textarea.selectionStart = newCursorPosition;
    textarea.selectionEnd = newCursorPosition;

    // Focus back on the textarea
    textarea.focus();
}
</script>
