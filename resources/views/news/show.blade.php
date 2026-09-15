@extends('layouts.app')

@section('content')

<style>
.news-show-page{color:#e5e7eb}
.news-show-hero{position:relative;overflow:hidden;padding:28px 30px;margin-bottom:18px;border-radius:.75rem;background:linear-gradient(135deg,rgba(22,32,51,.97),rgba(15,23,42,.91));border:1px solid var(--ui-border,rgba(255,255,255,.08));box-shadow:0 14px 36px rgba(0,0,0,.28)}
.hero-glow{position:absolute;top:-110px;right:-100px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(34,211,201,.12),transparent 70%);pointer-events:none}
.news-badge{display:inline-flex;align-items:center;padding:5px 9px;margin-bottom:12px;border-radius:.45rem;background:rgba(34,211,201,.08);border:1px solid rgba(34,211,201,.16);color:#67e8df;font-size:.68rem;font-weight:800;letter-spacing:.65px}
.news-title{position:relative;margin:0 0 13px;color:#f8fafc;font-size:clamp(1.65rem,4vw,2.5rem);line-height:1.2;font-weight:750;overflow-wrap:anywhere}
.news-meta{display:flex;flex-wrap:wrap;gap:14px 20px;color:rgba(226,232,240,.58);font-size:.78rem}
.news-meta span{display:inline-flex;align-items:center;gap:6px}.news-meta i{color:#5eead4}
.news-article-card{position:relative;overflow:hidden;border-radius:.75rem;background:linear-gradient(135deg,rgba(22,32,51,.94),rgba(15,23,42,.88));border:1px solid var(--ui-border,rgba(255,255,255,.07));box-shadow:0 12px 32px rgba(0,0,0,.24)}
.article-glow{position:absolute;bottom:-120px;left:-100px;width:230px;height:230px;border-radius:50%;background:radial-gradient(circle,rgba(34,211,201,.07),transparent 70%);pointer-events:none}
.news-content{position:relative;z-index:2;padding:30px;color:rgba(226,232,240,.82);font-size:.9rem;line-height:1.75;overflow-wrap:anywhere}
.news-content h1,.news-content h2,.news-content h3,.news-content h4,.news-content h5,.news-content h6{color:#f8fafc;margin-top:28px;margin-bottom:12px;font-weight:700;line-height:1.3}
.news-content h1:first-child,.news-content h2:first-child,.news-content h3:first-child,.news-content h4:first-child,.news-content h5:first-child,.news-content h6:first-child{margin-top:0}
.news-content p{margin-bottom:16px}.news-content ul,.news-content ol{margin-bottom:16px;padding-left:1.4rem}.news-content li{margin-bottom:5px}
.news-content img{display:block;max-width:100%;height:auto;border-radius:.65rem;margin:18px auto;border:1px solid rgba(255,255,255,.07);box-shadow:0 8px 24px rgba(0,0,0,.28)}
.news-content a{color:#5eead4;text-decoration:none}.news-content a:hover{color:#99f6e4;text-decoration:underline}
.news-content blockquote{margin:20px 0;padding:12px 17px;border-left:3px solid #14b8a6;background:rgba(34,211,201,.05);border-radius:.45rem;color:rgba(226,232,240,.72)}
.news-content code{background:rgba(255,255,255,.07);padding:2px 6px;border-radius:.3rem;color:#99f6e4;font-size:.84em}
.news-content pre{margin:18px 0;padding:14px;overflow-x:auto;border-radius:.55rem;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.06);color:#d1d5db}
.news-content table{width:100%;margin:18px 0;border-collapse:collapse}.news-content th,.news-content td{padding:8px 10px;border:1px solid rgba(255,255,255,.07)}.news-content th{background:rgba(34,211,201,.06);color:#f8fafc}
.news-actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:16px}.modern-btn{display:inline-flex;align-items:center;justify-content:center;padding:8px 13px;border-radius:.5rem;text-decoration:none;font-size:.78rem;font-weight:700;transition:all .2s ease}
.secondary-btn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:#dbe4ee}.secondary-btn:hover{background:rgba(255,255,255,.09);border-color:rgba(34,211,201,.2);color:#67e8df}
.edit-btn{background:rgba(34,211,201,.1);border:1px solid rgba(34,211,201,.22);color:#67e8df}.edit-btn:hover{background:#14b8a6;border-color:#14b8a6;color:#061311;box-shadow:0 5px 16px rgba(20,184,166,.18)}
.modern-btn:hover{transform:translateY(-1px)}
@media(max-width:768px){.news-show-page{padding-left:12px;padding-right:12px}.news-show-hero{padding:22px 18px}.news-title{font-size:1.55rem}.news-content{padding:22px 18px;font-size:.86rem}.news-actions{flex-direction:column}.modern-btn{width:100%}}
</style>

<div class="container py-4 news-show-page">
    <div class="news-show-hero">
        <div class="hero-glow"></div>
        <div class="position-relative">
            <div class="news-badge"><i class="bi bi-newspaper me-2"></i>FileIplay NEWS</div>
            <h1 class="news-title">{{ $news->title }}</h1>
            <div class="news-meta">
                <span><i class="bi bi-person-circle"></i>{{ $news->user->name }}</span>
                <span><i class="bi bi-calendar3"></i>{{ $news->created_at->format('F d, Y') }}</span>
            </div>
        </div>
    </div>

    <div class="news-article-card">
        <div class="article-glow"></div>
        <div class="news-content">{!! convertCustomTagsToHtml($news->content) !!}</div>
    </div>

    <div class="news-actions">
        <a href="{{ route('news.index') }}" class="modern-btn secondary-btn"><i class="bi bi-arrow-left-circle me-2"></i>Back to News</a>
        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
            <a href="{{ route('news.edit', $news) }}" class="modern-btn edit-btn"><i class="bi bi-pencil-square me-2"></i>Edit Article</a>
        @endif
    </div>
</div>

@endsection
