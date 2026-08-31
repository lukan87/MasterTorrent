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

/* =========================================
   WRAPPER
========================================= */

.modern-comments-wrapper{

    background:
        linear-gradient(
            145deg,
            rgba(20,25,40,.72),
            rgba(10,14,24,.92)
        );

    border-radius:24px;

    border:
        1px solid rgba(255,255,255,.06);

    overflow:hidden;

    backdrop-filter:blur(18px);

    box-shadow:
        0 20px 50px rgba(0,0,0,.45);
}

/* =========================================
   HEADER
========================================= */

.modern-comments-header{

    padding:22px 24px;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.comments-icon-box{

    width:58px;
    height:58px;

    border-radius:18px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:#fff;

    font-size:1.3rem;

    box-shadow:
        0 10px 25px rgba(59,130,246,.35);
}

.modern-comments-title{

    color:#fff;

    font-size:1.4rem;

    font-weight:800;
}

.modern-comments-subtitle{

    color:rgba(255,255,255,.58);

    font-size:.9rem;
}

/* =========================================
   BODY
========================================= */

.modern-comments-body{
    padding:24px;
}

/* =========================================
   ALERT
========================================= */

.modern-comment-alert{

    display:flex;

    align-items:flex-start;

    gap:14px;

    background:
        rgba(239,68,68,.12);

    border:
        1px solid rgba(239,68,68,.25);

    color:#fca5a5;

    padding:18px;

    border-radius:18px;

    margin-bottom:24px;
}

.modern-comment-alert i{

    font-size:1.3rem;
}

/* =========================================
   FORM
========================================= */

.modern-comment-form{

    background:rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);

    border-radius:22px;

    padding:22px;

    margin-bottom:30px;
}

/* =========================================
   TOOLBAR
========================================= */

.modern-editor-toolbar{

    display:flex;

    flex-wrap:wrap;

    gap:10px;

    margin-bottom:16px;
}

.toolbar-group{

    display:flex;

    gap:8px;
}

.modern-select{

    background:rgba(255,255,255,.06);

    border:
        1px solid rgba(255,255,255,.08);

    color:#fff;

    border-radius:12px;

    padding:10px 12px;
}

.toolbar-btn{

    border:none;

    background:rgba(255,255,255,.06);

    color:#fff;

    border-radius:12px;

    padding:10px 14px;

    font-weight:700;

    transition:.25s ease;
}

.toolbar-btn:hover{

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    transform:translateY(-2px);
}

/* =========================================
   TEXTAREA
========================================= */

.modern-comment-textarea{

    width:100%;

    background:rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.06);

    border-radius:18px;

    padding:18px;

    color:#fff;

    resize:vertical;

    min-height:140px;
}

.modern-comment-textarea:focus{

    outline:none;

    border-color:rgba(124,58,237,.4);

    box-shadow:
        0 0 0 4px rgba(124,58,237,.15);
}

/* =========================================
   BUTTON
========================================= */

.modern-submit-btn{

    border:none;

    border-radius:14px;

    padding:12px 18px;

    font-weight:700;

    color:#fff;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    transition:.25s ease;
}

.modern-submit-btn:hover{

    transform:translateY(-2px);

    box-shadow:
        0 12px 25px rgba(59,130,246,.25);
}

/* =========================================
   COMMENTS LIST
========================================= */

.modern-comments-list{

    display:flex;

    flex-direction:column;

    gap:18px;
}

/* =========================================
   COMMENT CARD
========================================= */

.modern-comment-card{

    background:rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);

    border-radius:22px;

    padding:22px;

    transition:.25s ease;
}

.modern-comment-card:hover{

    transform:translateY(-3px);

    border-color:rgba(124,58,237,.22);
}

/* =========================================
   TOP
========================================= */

.modern-comment-top{

    display:flex;

    justify-content:space-between;

    align-items:flex-start;

    gap:16px;

    margin-bottom:18px;
}

.comment-user{

    display:flex;

    align-items:center;

    gap:14px;
}

.comment-avatar{

    width:52px;
    height:52px;

    border-radius:16px;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    display:flex;

    align-items:center;
    justify-content:center;

    color:#fff;

    font-weight:800;

    font-size:1.1rem;
}

.comment-username{

    font-weight:800;

    font-size:1rem;
}

.comment-user-badge{

    padding:4px 10px;

    border-radius:999px;

    border:1px solid;

    background:rgba(255,255,255,.05);

    color:#fff;

    font-size:.72rem;

    font-weight:700;
}

.comment-time{

    color:rgba(255,255,255,.48);

    font-size:.82rem;

    margin-top:4px;
}

/* =========================================
   ACTIONS
========================================= */

.comment-actions{

    display:flex;

    gap:10px;
}

.comment-action-btn{

    width:42px;
    height:42px;

    border:none;

    border-radius:14px;

    display:flex;

    align-items:center;
    justify-content:center;

    color:#fff;

    transition:.25s ease;
}

.comment-action-btn:hover{

    transform:translateY(-2px);
}

.warning-btn{

    background:rgba(250,204,21,.18);

    color:#fde047;
}

.danger-btn{

    background:rgba(239,68,68,.18);

    color:#f87171;
}

/* =========================================
   CONTENT
========================================= */

.modern-comment-content{

    color:#e5e7eb;

    line-height:1.8;

    word-break:break-word;

    overflow-wrap:anywhere;
}

.modern-comment-content img{

    max-width:100%;

    border-radius:16px;

    margin:12px 0;
}

.modern-comment-content pre{

    background:rgba(0,0,0,.45);

    border-radius:16px;

    padding:16px;

    overflow:auto;
}

/* =========================================
   EMPTY
========================================= */

.empty-comments{

    text-align:center;

    padding:5px 10px;

    color:rgba(255,255,255,.55);
}

.empty-comments i{

    font-size:2rem;

    display:block;

    margin-bottom:16px;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-comments-body,
    .modern-comments-header{

        padding:18px;
    }

    .modern-comment-card{

        padding:18px;
    }

    .modern-comment-top{

        flex-direction:column;
    }

    .modern-editor-toolbar{

        overflow-x:auto;

        flex-wrap:nowrap;

        padding-bottom:6px;

        scrollbar-width:none;
    }

    .modern-editor-toolbar::-webkit-scrollbar{
        display:none;
    }

    .toolbar-btn,
    .modern-select{

        flex:0 0 auto;

        white-space:nowrap;
    }
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