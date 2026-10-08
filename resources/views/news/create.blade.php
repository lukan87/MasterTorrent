@extends('layouts.app')

@section('content')

<style>
.news-create-page{color:var(--theme-text, #e5e7eb)}
.news-create-hero,.news-form-card{position:relative;overflow:hidden;background:linear-gradient(135deg,var(--theme-surface, rgba(14,21,33,.96)),var(--theme-surface, rgba(10,15,27,.88)));border:1px solid var(--theme-border, rgba(148,163,184,.16));border-radius:.75rem;box-shadow:0 14px 34px var(--theme-shadow, rgba(0,0,0,.28))}
.news-create-hero{padding:28px 30px}.news-form-card{padding:28px 30px}
.hero-glow,.form-glow{position:absolute;pointer-events:none;border-radius:50%;filter:blur(8px)}
.hero-glow{width:260px;height:260px;top:-150px;right:-110px;background:radial-gradient(circle,var(--theme-teal-soft, rgba(20,184,166,.12)),transparent 68%)}
.form-glow{width:240px;height:240px;bottom:-150px;left:-120px;background:radial-gradient(circle,var(--theme-teal-soft, rgba(20,184,166,.08)),transparent 68%)}
.hero-badge{display:inline-flex;align-items:center;padding:.42rem .7rem;margin-bottom:.8rem;border-radius:.5rem;background:var(--theme-teal-soft, rgba(20,184,166,.09));border:1px solid var(--theme-teal-border, rgba(20,184,166,.22));color:var(--theme-teal-text, #67e8df);font-size:var(--site-font-small, 13px);font-weight:800;letter-spacing:.7px}
.hero-title{margin:0 0 .45rem;color:var(--theme-text, #f8fafc);font-size:clamp(1.7rem,3vw,2.35rem);line-height:1.15;font-weight:800}
.hero-subtitle{margin:0;color:var(--theme-muted, #94a3b8);font-size:var(--site-font-body, 13px)}
.modern-label{display:flex;align-items:center;margin-bottom:.55rem;color:var(--theme-text, #cbd5e1);font-size:var(--site-font-body, 13px);font-weight:700;letter-spacing:.35px}
.modern-label i{color:var(--theme-teal-text, #67e8df)}
.modern-input,.modern-textarea{background:var(--theme-control, rgba(1,4,15,.58))!important;border:1px solid var(--theme-border, rgba(148,163,184,.18))!important;border-radius:.55rem!important;color:var(--theme-text, #f8fafc)!important;padding:.72rem .85rem!important;box-shadow:none!important;font-size:var(--site-font-body, 13px)}
.modern-input::placeholder,.modern-textarea::placeholder{color:var(--theme-muted, #64748b)}
.modern-input:focus,.modern-textarea:focus{border-color:var(--theme-teal-border, rgba(20,184,166,.55))!important;box-shadow:0 0 0 .18rem var(--theme-shadow, rgba(20,184,166,.10))!important}
.modern-textarea{min-height:280px;resize:vertical;line-height:1.65}
.editor-toolbar{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.65rem}
.toolbar-btn{width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--theme-border, rgba(148,163,184,.16));border-radius:.5rem;background:var(--theme-surface, rgba(20,27,38,.72));color:var(--theme-text, #cbd5e1);transition:background .18s ease,border-color .18s ease,color .18s ease,transform .18s ease}
.toolbar-btn:hover{transform:translateY(-1px);background:var(--theme-teal-soft, rgba(20,184,166,.12));border-color:var(--theme-teal-border, rgba(20,184,166,.32));color:var(--theme-teal-text, #67e8df)}
.danger-btn:hover{background:var(--theme-red-soft, rgba(239,68,68,.11));border-color:var(--theme-red-border, rgba(239,68,68,.28));color:var(--theme-red-text, #fca5a5)}
.success-btn:hover{background:var(--theme-green-soft, rgba(34,197,94,.11));border-color:var(--theme-green-border, rgba(34,197,94,.28));color:var(--theme-green-text, #86efac)}
.publish-btn,.cancel-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:.62rem 1rem;border-radius:.55rem;font-size:var(--site-font-body, 13px);font-weight:700;text-decoration:none;transition:transform .18s ease,background .18s ease,border-color .18s ease}
.publish-btn{border:1px solid var(--theme-teal-border, rgba(20,184,166,.35));background:var(--theme-teal-soft, rgba(20,184,166,.14));color:var(--theme-teal-text, #67e8df)}
.publish-btn:hover{transform:translateY(-1px);background:var(--theme-teal-soft, rgba(20,184,166,.22));border-color:var(--theme-teal-border, rgba(20,184,166,.5));color:var(--theme-teal-text, #99f6ef)}
.cancel-btn{border:1px solid var(--theme-border, rgba(148,163,184,.18));background:var(--theme-surface, rgba(20,27,38,.65));color:var(--theme-text, #cbd5e1)}
.cancel-btn:hover{transform:translateY(-1px);background:var(--theme-surface, rgba(33,42,55,.8));border-color:var(--theme-border, rgba(148,163,184,.3));color:var(--theme-text, #f8fafc)}
@@media (max-width:768px){.news-create-page{padding-top:1.25rem!important;padding-bottom:1.25rem!important}.news-create-hero,.news-form-card{padding:20px;border-radius:.65rem}.hero-title{font-size:1.7rem}.hero-subtitle{font-size:var(--site-font-body, 13px)}.modern-textarea{min-height:240px}.publish-btn,.cancel-btn{width:100%}}
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
