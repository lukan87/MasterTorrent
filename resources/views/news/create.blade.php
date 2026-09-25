@extends('layouts.app')

@section('content')

<style>
.news-create-page{color:#e5e7eb}
.news-create-hero,.news-form-card{position:relative;overflow:hidden;background:linear-gradient(135deg,rgba(22,32,51,.96),rgba(15,23,42,.88));border:1px solid rgba(148,163,184,.16);border-radius:.75rem;box-shadow:0 14px 34px rgba(0,0,0,.28)}
.news-create-hero{padding:28px 30px}.news-form-card{padding:28px 30px}
.hero-glow,.form-glow{position:absolute;pointer-events:none;border-radius:50%;filter:blur(8px)}
.hero-glow{width:260px;height:260px;top:-150px;right:-110px;background:radial-gradient(circle,rgba(20,184,166,.12),transparent 68%)}
.form-glow{width:240px;height:240px;bottom:-150px;left:-120px;background:radial-gradient(circle,rgba(20,184,166,.08),transparent 68%)}
.hero-badge{display:inline-flex;align-items:center;padding:.42rem .7rem;margin-bottom:.8rem;border-radius:.5rem;background:rgba(20,184,166,.09);border:1px solid rgba(20,184,166,.22);color:#67e8df;font-size:.72rem;font-weight:800;letter-spacing:.7px}
.hero-title{margin:0 0 .45rem;color:#f8fafc;font-size:clamp(1.7rem,3vw,2.35rem);line-height:1.15;font-weight:800}
.hero-subtitle{margin:0;color:#94a3b8;font-size:.9rem}
.modern-label{display:flex;align-items:center;margin-bottom:.55rem;color:#cbd5e1;font-size:.78rem;font-weight:700;letter-spacing:.35px}
.modern-label i{color:#67e8df}
.modern-input,.modern-textarea{background:rgba(2,6,23,.58)!important;border:1px solid rgba(148,163,184,.18)!important;border-radius:.55rem!important;color:#f8fafc!important;padding:.72rem .85rem!important;box-shadow:none!important;font-size:.9rem}
.modern-input::placeholder,.modern-textarea::placeholder{color:#64748b}
.modern-input:focus,.modern-textarea:focus{border-color:rgba(20,184,166,.55)!important;box-shadow:0 0 0 .18rem rgba(20,184,166,.10)!important}
.modern-textarea{min-height:280px;resize:vertical;line-height:1.65}
.editor-toolbar{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.65rem}
.toolbar-btn{width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(148,163,184,.16);border-radius:.5rem;background:rgba(30,41,59,.72);color:#cbd5e1;transition:background .18s ease,border-color .18s ease,color .18s ease,transform .18s ease}
.toolbar-btn:hover{transform:translateY(-1px);background:rgba(20,184,166,.12);border-color:rgba(20,184,166,.32);color:#67e8df}
.danger-btn:hover{background:rgba(239,68,68,.11);border-color:rgba(239,68,68,.28);color:#fca5a5}
.success-btn:hover{background:rgba(34,197,94,.11);border-color:rgba(34,197,94,.28);color:#86efac}
.publish-btn,.cancel-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:.62rem 1rem;border-radius:.55rem;font-size:.84rem;font-weight:700;text-decoration:none;transition:transform .18s ease,background .18s ease,border-color .18s ease}
.publish-btn{border:1px solid rgba(20,184,166,.35);background:rgba(20,184,166,.14);color:#67e8df}
.publish-btn:hover{transform:translateY(-1px);background:rgba(20,184,166,.22);border-color:rgba(20,184,166,.5);color:#99f6ef}
.cancel-btn{border:1px solid rgba(148,163,184,.18);background:rgba(30,41,59,.65);color:#cbd5e1}
.cancel-btn:hover{transform:translateY(-1px);background:rgba(51,65,85,.8);border-color:rgba(148,163,184,.3);color:#f8fafc}
@@media (max-width:768px){.news-create-page{padding-top:1.25rem!important;padding-bottom:1.25rem!important}.news-create-hero,.news-form-card{padding:20px;border-radius:.65rem}.hero-title{font-size:1.7rem}.hero-subtitle{font-size:.84rem}.modern-textarea{min-height:240px}.publish-btn,.cancel-btn{width:100%}}
@@media (prefers-reduced-motion:reduce){.toolbar-btn,.publish-btn,.cancel-btn{transition:none}}
</style>

<div class="container py-5 news-create-page">
    <div class="news-create-hero mb-4">
        <div class="hero-glow"></div>
        <div class="position-relative z-2">
            <div class="hero-badge"><i class="bi bi-megaphone-fill me-2"></i>STAFF NEWS PANEL</div>
            <h1 class="hero-title">Create News Article</h1>
            <p class="hero-subtitle">Publish announcements, updates and tracker news in a modern format.</p>
        </div>
    </div>

    <div class="news-form-card">
        <div class="form-glow"></div>
        <div class="position-relative z-2">
            <form action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label for="title" class="modern-label"><i class="bi bi-type me-2"></i>Article Title</label>
                    <input type="text" name="title" id="title" class="form-control modern-input" placeholder="Enter a catchy news title..." value="{{ old('title') }}" required>
                </div>

                <div class="mb-2">
                    <label for="content" class="modern-label"><i class="bi bi-pencil-square me-2"></i>Article Content</label>
                    <div class="editor-toolbar">
                        <button type="button" class="toolbar-btn" onclick="insertTag('b')" title="Bold"><i class="bi bi-type-bold"></i></button>
                        <button type="button" class="toolbar-btn" onclick="insertTag('i')" title="Italic"><i class="bi bi-type-italic"></i></button>
                        <button type="button" class="toolbar-btn" onclick="insertTag('u')" title="Underline"><i class="bi bi-type-underline"></i></button>
                        <button type="button" class="toolbar-btn" onclick="insertTag('center')" title="Center"><i class="bi bi-text-center"></i></button>
                        <button type="button" class="toolbar-btn" onclick="insertTag('quote')" title="Quote"><i class="bi bi-chat-left-quote"></i></button>
                        <button type="button" class="toolbar-btn danger-btn" onclick="insertTag('youtube')" title="YouTube"><i class="bi bi-youtube"></i></button>
                        <button type="button" class="toolbar-btn success-btn" onclick="insertTag('img')" title="Image"><i class="bi bi-image"></i></button>
                    </div>
                </div>

                <div class="mb-4">
                    <textarea name="content" id="content" rows="12" class="form-control modern-textarea" placeholder="Write your news article here..." required>{{ old('content') }}</textarea>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <button type="submit" class="publish-btn"><i class="bi bi-send-fill me-2"></i>Publish News</button>
                    <a href="{{ route('news.index') }}" class="cancel-btn"><i class="bi bi-arrow-left-circle me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function insertTag(tag) {
    const textarea = document.getElementById('content');
    if (!textarea) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.substring(start, end);
    const openTag = `[${tag}]`;
    const closeTag = `[/${tag}]`;
    const replacement = openTag + selected + closeTag;
    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    textarea.selectionStart = start + openTag.length;
    textarea.selectionEnd = end + openTag.length;
}
</script>

@endsection
