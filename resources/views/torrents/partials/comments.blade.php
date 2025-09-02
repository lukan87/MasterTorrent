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
                <!-- BBCode & Formatting Buttons -->
                <div class="mb-2 d-flex flex-wrap gap-2">
                    <select id="fontSize" class="form-select form-select-sm d-inline-block" style="width:auto;">
                        <option value="14">1 (Small)</option>
                        <option value="16">2 (Normal)</option>
                        <option value="18">3 (Medium)</option>
                        <option value="20">4 (Large)</option>
                        <option value="22">5 (Extra Large)</option>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('size', document.getElementById('fontSize').value)">Use Size</button>

                    <select id="fontColor" class="form-select form-select-sm d-inline-block" style="width:auto;">
                        <option value="black">Black</option>
                        <option value="red">Red</option>
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="purple">Purple</option>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('color', document.getElementById('fontColor').value)">Use Color</button>

                    <select id="fontFamily" class="form-select form-select-sm d-inline-block" style="width:auto;">
                        <option value="Arial">Arial</option>
                        <option value="Verdana">Verdana</option>
                        <option value="Courier">Courier</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Times New Roman">Times New Roman</option>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('font', document.getElementById('fontFamily').value)">Use Font Family</button>

                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('center')">Center</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('b')">Bold</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('i')">Italic</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('u')">Underline</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('quote')">Quote</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('youtube')">YouTube</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('img')">Image</button>
                </div>

                <!-- Emoji Toolbar -->
                <div class="emoji-toolbar mb-2">
                    @foreach(['😀'=>'Grinning Face','😁'=>'Beaming Face','😂'=>'Tears of Joy','🤣'=>'Rolling on the Floor','😊'=>'Smiling Eyes','😍'=>'Heart Eyes','😘'=>'Face Blowing Kiss','😎'=>'Sunglasses','😢'=>'Crying','😭'=>'Loudly Crying','😡'=>'Pouting','👍'=>'Thumbs Up','👎'=>'Thumbs Down','👏'=>'Clapping','🙏'=>'Folded Hands','🔥'=>'Fire','💯'=>'Hundred Points','🎉'=>'Party Popper','💥'=>'Collision','🌹'=>'Rose','🍀'=>'Clover','⭐'=>'Star','⚡'=>'High Voltage','☀️'=>'Sun','🌙'=>'Crescent Moon','🍕'=>'Pizza','🍔'=>'Burger','🍺'=>'Beer','🎵'=>'Musical Note','🎮'=>'Video Game','⚽'=>'Soccer','🏀'=>'Basketball'] as $emoji=>$title)
                        <button type="button" class="emoji-btn btn btn-light btn-sm" data-bs-toggle="tooltip" title="{{ $emoji }} {{ $title }}" onclick="insertSmiley('{{ $emoji }}')">{{ $emoji }}</button>
                    @endforeach
                </div>

                <textarea id="comment" name="comment" class="form-control" rows="6" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Comment</button>
        </form>

        <hr>

        <!-- Display Comments -->
        @if($comments->isEmpty())
            <p>No comments yet</p>
        @else
            @foreach($comments as $comment)
                <div class="card mb-3 card-blur">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div class="comment-details flex-grow-1">
                            <h5 class="card-subtitle mb-2 text-muted">{{ $comment->user->name ?? 'Unknown' }} <b>@ {{ $comment->created_at }}</b></h5>
                            <p class="card-text">{!! convertCustomTagsToHtml($comment->comment) !!}</p>
                        </div>
                        <div class="comment-actions d-flex flex-column gap-2">
                            @if(Auth::user()->user_class > 5 || $comment->user_id == Auth::id())
                                <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#edit-comment-{{ $comment->id }}" title="Edit Comment"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('comments.destroy', $comment->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Comment"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div id="edit-comment-{{ $comment->id }}" class="collapse">
                        <form action="{{ route('comments.update', ['id'=>$comment->id]) }}" method="POST">
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
            {{ $comments->links() }}
        @endif
    </div>
</div>

<style>
.card-blur {
    backdrop-filter: blur(1px);
    -webkit-backdrop-filter: blur(6px);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(20,20,20,0.6);
}

.emoji-toolbar {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 6px 0;
    border-radius: 6px;
    background: rgba(255,255,255,0.05);
}

.emoji-btn {
    font-size: 20px;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.emoji-btn:hover {
    transform: scale(1.3);
}

.emoji-pop {
    position: absolute;
    font-size: 28px;
    pointer-events: none;
    animation: popUp 1.2s ease forwards;
    z-index: 9999;
}

@keyframes popUp {
    0% { transform: scale(0.5); opacity: 0; }
    30% { transform: scale(1.3); opacity: 1; }
    100% { transform: translateY(-80px) rotate(20deg) scale(1); opacity: 0; }
}

.comment-actions button {
    width: 100%;
}
</style>

<script>
function insertBBCode(tag, option=null){
    const textarea = document.getElementById("comment");
    const startTag = option ? `[${tag}=${option}]` : `[${tag}]`;
    const endTag = `[/${tag}]`;
    const cursorPos = textarea.selectionStart;
    const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
    const newText = startTag + selectedText + endTag;
    textarea.value = textarea.value.substring(0,cursorPos)+newText+textarea.value.substring(textarea.selectionEnd);
    textarea.selectionStart = textarea.selectionEnd = cursorPos + startTag.length;
    textarea.focus();
}

function insertSmiley(smiley){
    const textarea = document.getElementById("comment");
    const cursorPos = textarea.selectionStart;
    const before = textarea.value.substring(0,cursorPos);
    const after = textarea.value.substring(textarea.selectionEnd);
    textarea.value = before + smiley + after;
    textarea.selectionStart = textarea.selectionEnd = cursorPos + smiley.length;
    textarea.focus();

    const emojiEl = document.createElement('span');
    emojiEl.textContent = smiley;
    emojiEl.className = 'emoji-pop';
    document.body.appendChild(emojiEl);

    const rect = textarea.getBoundingClientRect();
    const randX = Math.random()*100-50;
    emojiEl.style.left = (rect.left+rect.width/2+randX)+'px';
    emojiEl.style.top = (rect.top+rect.height/2)+'px';

    setTimeout(()=>emojiEl.remove(),1200);
}
</script>
