<div class="modern-comments-wrapper mt-4">

    {{-- =====================================
        HEADER
    ====================================== --}}
    <div class="modern-comments-header">

        <div class="d-flex align-items-center gap-3">

            <div class="comments-icon-box">

                <i class="bi bi-chat-left-text-fill"></i>

            </div>

            <div>

                <h4 class="modern-comments-title mb-1">

                    Comments

                </h4>

                <div class="modern-comments-subtitle">

                    Discussion for {{ $torrent->name }}

                </div>

            </div>

        </div>

    </div>

    {{-- =====================================
        BODY
    ====================================== --}}
    <div class="modern-comments-body">

        {{-- =====================================
            COMMENT BLOCK
        ====================================== --}}
        @auth

            @if(auth()->user()->commentblock)

                <div class="modern-comment-alert">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                    <div>

                        <strong>Commenting Disabled</strong>

                        <div class="small mt-1">

                            Your account is currently restricted from posting comments.

                        </div>

                    </div>

                </div>

            @else

                {{-- =====================================
                    COMMENT FORM
                ====================================== --}}
                <form action="{{ route('comments.store') }}"
                      method="POST"
                      class="modern-comment-form">

                    @csrf

                    <input type="hidden"
                           name="commentable_id"
                           value="{{ $torrent->id }}">

                    <input type="hidden"
                           name="commentable_type"
                           value="torrent">

                    <input type="hidden"
                           name="torrent_id"
                           value="{{ $torrent->id }}">

                    {{-- TOOLBAR --}}
                    <div class="modern-editor-toolbar">

                        <div class="toolbar-group">

                            <select id="fontSize"
                                    class="modern-select">

                                <option value="14">Small</option>
                                <option value="16">Normal</option>
                                <option value="18">Medium</option>
                                <option value="20">Large</option>
                                <option value="22">Extra Large</option>

                            </select>

                            <button type="button"
                                    class="toolbar-btn"
                                    onclick="insertBBCode('size', document.getElementById('fontSize').value)">

                                Size

                            </button>

                        </div>

                        <div class="toolbar-group">

                            <select id="fontColor"
                                    class="modern-select">

                                <option value="black">Black</option>
                                <option value="red">Red</option>
                                <option value="blue">Blue</option>
                                <option value="green">Green</option>
                                <option value="purple">Purple</option>

                            </select>

                            <button type="button"
                                    class="toolbar-btn"
                                    onclick="insertBBCode('color', document.getElementById('fontColor').value)">

                                Color

                            </button>

                        </div>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertBBCode('b')">

                            <i class="bi bi-type-bold"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertBBCode('i')">

                            <i class="bi bi-type-italic"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertBBCode('u')">

                            <i class="bi bi-type-underline"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertBBCode('quote')">

                            <i class="bi bi-blockquote-left"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertBBCode('img')">

                            <i class="bi bi-image"></i>

                        </button>

                    </div>

                    {{-- TEXTAREA --}}
                    <textarea id="comment"
                              name="comment"
                              class="modern-comment-textarea"
                              rows="6"
                              placeholder="Write your comment..."
                              required></textarea>

                    {{-- SUBMIT --}}
                    <div class="mt-3">

                        <button type="submit"
                                class="modern-submit-btn">

                            <i class="bi bi-send-fill me-2"></i>

                            Post Comment

                        </button>

                    </div>

                </form>

            @endif

        @endauth

        {{-- =====================================
            COMMENTS LIST
        ====================================== --}}
        <div class="modern-comments-list">

            @if($comments->isEmpty())

                <div class="empty-comments">

                    <i class="bi bi-chat-square-text"></i>

                    <p>

                        No comments yet

                    </p>

                </div>

            @else

                @foreach($comments as $comment)

                    <div class="modern-comment-card">

                        {{-- TOP --}}
                        <div class="modern-comment-top">

                            <div class="comment-user">

                                <div class="comment-avatar">

                                    {{ strtoupper(substr($comment->user->name ?? 'U',0,1)) }}

                                </div>

                                <div>

                                    <div class="d-flex align-items-center flex-wrap gap-2">

                                        <span class="comment-username"
                                              style="color: {{ \App\Models\UserClass::getClassColor($comment->user->user_class ?? 1) }}">

                                            {{ $comment->user->name ?? 'Unknown' }}

                                        </span>

                                        <span class="comment-user-badge"
                                              style="border-color: {{ \App\Models\UserClass::getClassColor($comment->user->user_class ?? 1) }}">

                                            {{ \App\Models\UserClass::getClassName($comment->user->user_class ?? 1) }}

                                        </span>

                                    </div>

                                    <div class="comment-time">

                                        {{ $comment->created_at->diffForHumans() }}

                                    </div>

                                </div>

                            </div>

                            {{-- ACTIONS --}}
                            @if(Auth::user()->user_class > 5 || $comment->user_id == Auth::id())

                                <div class="comment-actions">

                                    <button class="comment-action-btn warning-btn"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#edit-comment-{{ $comment->id }}">

                                        <i class="bi bi-pencil"></i>

                                    </button>

                                    <form action="{{ route('comments.destroy', $comment->id) }}"
                                          method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="comment-action-btn danger-btn">

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            @endif

                        </div>

                        {{-- COMMENT --}}
                        <div class="modern-comment-content">

                            {!! convertCustomTagsToHtml($comment->comment) !!}

                        </div>

                        {{-- EDIT --}}
                        <div id="edit-comment-{{ $comment->id }}"
                             class="collapse mt-4">

                            <form action="{{ route('comments.update', ['id'=>$comment->id]) }}"
                                  method="POST">

                                @csrf
                                @method('PUT')

                                <textarea name="comment"
                                          class="modern-comment-textarea"
                                          rows="4"
                                          required>{{ $comment->comment }}</textarea>

                                <button type="submit"
                                        class="modern-submit-btn mt-3">

                                    Save Changes

                                </button>

                            </form>

                        </div>

                    </div>

                @endforeach

                <div class="mt-4">

                    {{ $comments->links('pagination::bootstrap-5') }}

                </div>

            @endif

        </div>

    </div>

</div>



<style>
.modern-comments-wrapper{
    background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.84));
    border:1px solid var(--ui-border);
    border-radius:.85rem;
    overflow:hidden;
    backdrop-filter:blur(14px);
    box-shadow:0 12px 30px rgba(0,0,0,.28);
    position:relative;
}
.modern-comments-wrapper::before{
    content:"";position:absolute;left:0;top:0;bottom:0;width:3px;
    background:linear-gradient(180deg,var(--ui-accent),var(--ui-accent-strong));
}
.modern-comments-header{
    padding:16px 20px;border-bottom:1px solid var(--ui-border);
}
.comments-icon-box{
    width:44px;height:44px;border-radius:.7rem;display:flex;align-items:center;justify-content:center;
    background:rgba(20,184,166,.12);border:1px solid rgba(20,184,166,.28);
    color:var(--ui-accent);font-size:1.05rem;
}
.modern-comments-title{color:#fff;font-size:14px;font-weight:700;margin:0}
.modern-comments-subtitle{color:rgba(255,255,255,.58);font-size:13px}
.modern-comments-body{padding:18px 20px}
.modern-comment-alert{
    display:flex;align-items:flex-start;gap:12px;background:rgba(239,68,68,.10);
    border:1px solid rgba(239,68,68,.24);color:#fca5a5;padding:14px;border-radius:.7rem;margin-bottom:18px;font-size:14px;
}
.modern-comment-form{
    background:rgba(255,255,255,.025);border:1px solid var(--ui-border);
    border-radius:.75rem;padding:16px;margin-bottom:22px;
}
.modern-editor-toolbar{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
.toolbar-group{display:flex;gap:6px}
.modern-select,.toolbar-btn{
    background:rgba(255,255,255,.045);border:1px solid var(--ui-border);
    color:#e5e7eb;border-radius:.55rem;padding:7px 10px;font-size:13px;
}
.modern-select option{background:#111827;color:#fff}
.toolbar-btn{font-weight:600;transition:.2s ease}
.toolbar-btn:hover{background:rgba(20,184,166,.12);border-color:rgba(20,184,166,.35);color:var(--ui-accent)}
.modern-comment-textarea{
    width:100%;background:rgba(15,23,42,.72);border:1px solid var(--ui-border);
    border-radius:.7rem;padding:13px;color:#fff;resize:vertical;min-height:130px;font-size:14px;
}
.modern-comment-textarea::placeholder{color:rgba(255,255,255,.38)}
.modern-comment-textarea:focus{
    outline:none;border-color:var(--ui-accent);
    box-shadow:0 0 0 3px rgba(20,184,166,.10);
}
.modern-submit-btn{
    border:1px solid rgba(20,184,166,.35);border-radius:.6rem;padding:9px 14px;
    font-size:14px;font-weight:700;color:#fff;background:rgba(20,184,166,.12);transition:.2s ease;
}
.modern-submit-btn:hover{background:rgba(20,184,166,.2);border-color:var(--ui-accent);color:#fff;transform:translateY(-1px)}
.modern-comments-list{display:flex;flex-direction:column;gap:12px}
.modern-comment-card{
    background:rgba(255,255,255,.025);border:1px solid var(--ui-border);
    border-radius:.75rem;padding:16px;transition:.2s ease;
}
.modern-comment-card:hover{border-color:rgba(20,184,166,.28)}
.modern-comment-top{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:14px}
.comment-user{display:flex;align-items:center;gap:11px}
.comment-avatar{
    width:42px;height:42px;border-radius:.65rem;display:flex;align-items:center;justify-content:center;
    background:rgba(20,184,166,.10);border:1px solid rgba(20,184,166,.25);
    color:var(--ui-accent);font-weight:700;font-size:14px;
}
.comment-username{font-weight:700;font-size:14px}
.comment-user-badge{
    padding:3px 8px;border-radius:999px;border:1px solid;background:rgba(255,255,255,.035);
    color:#fff;font-size:11px;font-weight:600
}
.comment-time{color:rgba(255,255,255,.45);font-size:12px;margin-top:3px}
.comment-actions{display:flex;gap:6px}
.comment-action-btn{
    width:34px;height:34px;border:1px solid var(--ui-border);border-radius:.55rem;
    display:flex;align-items:center;justify-content:center;color:#fff;background:rgba(255,255,255,.035);transition:.2s ease;
}
.comment-action-btn:hover{transform:translateY(-1px)}
.warning-btn{background:rgba(250,204,21,.10);color:#fde047}
.danger-btn{background:rgba(239,68,68,.10);color:#f87171}
.modern-comment-content{color:#e5e7eb;font-size:14px;line-height:1.65;word-break:break-word;overflow-wrap:anywhere}
.modern-comment-content img{max-width:100%;height:auto;border-radius:.65rem;margin:10px 0}
.modern-comment-content pre{background:rgba(0,0,0,.35);border:1px solid var(--ui-border);border-radius:.65rem;padding:12px;overflow:auto}
.empty-comments{text-align:center;padding:32px 10px;color:rgba(255,255,255,.5);font-size:14px}
.empty-comments i{font-size:2rem;display:block;margin-bottom:10px;color:var(--ui-accent)}
@media(max-width:768px){
 .modern-comments-body,.modern-comments-header{padding:14px}
 .modern-comment-card,.modern-comment-form{padding:14px}
 .modern-comment-top{flex-direction:column}
 .modern-editor-toolbar{overflow-x:auto;flex-wrap:nowrap;padding-bottom:5px;scrollbar-width:none}
 .modern-editor-toolbar::-webkit-scrollbar{display:none}
 .toolbar-btn,.modern-select{flex:0 0 auto;white-space:nowrap}
 .modern-comment-content{font-size:14px}
}
</style>



<script>

function insertBBCode(tag, option=null){

    const textarea =
        document.getElementById("comment");

    const startTag =
        option
        ? `[${tag}=${option}]`
        : `[${tag}]`;

    const endTag =
        `[/${tag}]`;

    const cursorPos =
        textarea.selectionStart;

    const selectedText =
        textarea.value.substring(
            textarea.selectionStart,
            textarea.selectionEnd
        );

    const newText =
        startTag +
        selectedText +
        endTag;

    textarea.value =
        textarea.value.substring(0,cursorPos) +
        newText +
        textarea.value.substring(textarea.selectionEnd);

    textarea.selectionStart =
    textarea.selectionEnd =
        cursorPos + startTag.length;

    textarea.focus();
}

</script>