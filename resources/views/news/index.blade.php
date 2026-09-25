@extends('layouts.app')

@section('content')

<div class="container py-5 news-page">

    {{-- =========================================
        HERO HEADER
    ========================================= --}}
    <div class="news-hero mb-5">

        <div class="hero-glow"></div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative">

            <div>

                <div class="news-kicker">

                    FileIplay UPDATES

                </div>

                <h1 class="news-title">

                    News Center

                </h1>

                <p class="news-subtitle">

                    Tracker updates, announcements and community news

                </p>

            </div>

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

            <a href="{{ route('news.create') }}"
               class="create-news-btn">

                <i class="bi bi-plus-circle-fill me-2"></i>

                Create News

            </a>
            @endif

        </div>

    </div>

    {{-- =========================================
        NEWS LIST
    ========================================= --}}
    <div class="news-grid">

        @forelse($newsItems as $news)

            <div class="news-card">

                <div class="news-card-glow"></div>

                <div class="news-card-body">

                    {{-- DATE --}}
                    <div class="news-date">

                        <i class="bi bi-calendar3 me-2"></i>

                        {{ $news->created_at->format('F j, Y') }}

                    </div>

                    {{-- TITLE --}}
                    <h3 class="news-card-title">

                        <a href="{{ route('news.show', $news) }}">

                            {{ $news->title }}

                        </a>

                    </h3>

                    {{-- AUTHOR --}}
                    <div class="news-author">

                        <i class="bi bi-person-circle me-2"></i>

                        Posted by

                        <span>

                            {{ $news->user->name }}

                        </span>

                    </div>

                    {{-- ACTIONS --}}
                    <div class="news-footer">

                        <a href="{{ route('news.show', $news) }}"
                           class="read-more-btn">

                            Read Article

                            <i class="bi bi-arrow-right-short"></i>

                        </a>

                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                            <div class="admin-actions">

                                <a href="{{ route('news.edit', $news) }}"
                                   class="admin-btn edit-btn">

                                    <i class="bi bi-pencil-square"></i>

                                </a>

                                <form action="{{ route('news.destroy', $news) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="admin-btn delete-btn"
                                            onclick="return confirm('Are you sure?')">

                                        <i class="bi bi-trash-fill"></i>

                                    </button>

                                </form>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        @empty

            <div class="empty-news-card">

                <div class="empty-icon">

                    <i class="bi bi-newspaper"></i>

                </div>

                <h3>

                    No news articles available

                </h3>

                <p>

                    Be the first to create a news article.

                </p>

                <a href="{{ route('news.create') }}"
                   class="create-news-btn mt-2">

                    <i class="bi bi-plus-circle-fill me-2"></i>

                    Create News

                </a>

            </div>

        @endforelse

    </div>

</div>

<style>
/* FILEIPLAY NEWS CENTER */
.news-page { color:#e5e7eb; }
.news-hero,.news-card,.empty-news-card,.modern-alert {
    background:linear-gradient(135deg,rgba(22,32,51,.97),rgba(15,23,42,.90));
    border:1px solid var(--ui-border,rgba(255,255,255,.08));
    box-shadow:0 12px 32px rgba(0,0,0,.25);
}
.news-hero {
    position:relative; overflow:hidden; padding:28px 30px; border-radius:.75rem;
}
.hero-glow,.news-card-glow { position:absolute; border-radius:50%; pointer-events:none; }
.hero-glow { top:-110px; right:-100px; width:260px; height:260px; background:radial-gradient(circle,rgba(34,211,201,.12),transparent 70%); }
.news-kicker { color:#67e8df; font-size:.7rem; font-weight:800; letter-spacing:1.4px; margin-bottom:7px; }
.news-title { color:#f8fafc; font-size:1.7rem; font-weight:750; margin:0; }
.news-subtitle { margin:7px 0 0; color:rgba(226,232,240,.58); font-size:.82rem; }
.create-news-btn { display:inline-flex; align-items:center; justify-content:center; padding:8px 13px; border-radius:.5rem; background:rgba(34,211,201,.1); border:1px solid rgba(34,211,201,.22); color:#67e8df; text-decoration:none; font-size:.78rem; font-weight:700; transition:.2s ease; }
.create-news-btn:hover { background:#14b8a6; border-color:#14b8a6; color:#061311; transform:translateY(-1px); }
.modern-alert { padding:12px 15px; border-radius:.6rem; color:#d1fae5; }
.success-alert { background:rgba(34,197,94,.08); border-color:rgba(34,197,94,.18); }
.alert-icon { color:#4ade80; }
.news-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:12px; }
.news-card { position:relative; overflow:hidden; border-radius:.7rem; transition:.2s ease; }
.news-card:hover { transform:translateY(-2px); border-color:rgba(34,211,201,.23); box-shadow:0 16px 34px rgba(0,0,0,.32); }
.news-card-glow { top:-70px; right:-70px; width:160px; height:160px; background:radial-gradient(circle,rgba(34,211,201,.07),transparent 70%); }
.news-card-body { position:relative; z-index:2; padding:18px; }
.news-date { color:#67e8df; font-size:.72rem; font-weight:700; margin-bottom:9px; }
.news-card-title { margin:0 0 9px; font-size:1.02rem; line-height:1.4; font-weight:700; }
.news-card-title a { color:#f8fafc; text-decoration:none; transition:.2s ease; }
.news-card-title a:hover { color:#67e8df; }
.news-author { color:rgba(226,232,240,.52); font-size:.74rem; margin-bottom:17px; }
.news-author span { color:#cbd5e1; font-weight:700; }
.news-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
.read-more-btn { display:inline-flex; align-items:center; gap:3px; color:#5eead4; text-decoration:none; font-size:.76rem; font-weight:700; transition:.2s ease; }
.read-more-btn:hover { gap:6px; color:#99f6e4; }
.admin-actions { display:flex; gap:6px; }
.admin-btn { width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:.45rem; border:1px solid transparent; text-decoration:none; transition:.2s ease; }
.edit-btn { background:rgba(34,211,201,.08); color:#67e8df; border-color:rgba(34,211,201,.12); }
.delete-btn { background:rgba(239,68,68,.08); color:#f87171; border-color:rgba(239,68,68,.12); }
.admin-btn:hover { transform:translateY(-1px); }
.empty-news-card { grid-column:1/-1; text-align:center; padding:55px 25px; border-radius:.75rem; }
.empty-icon { font-size:2.7rem; color:rgba(94,234,212,.25); margin-bottom:12px; }
.empty-news-card h3 { color:#f8fafc; font-size:1.05rem; font-weight:700; margin-bottom:7px; }
.empty-news-card p { color:rgba(226,232,240,.55); font-size:.8rem; margin-bottom:18px; }
@media(max-width:768px){
    .news-page { padding-left:12px; padding-right:12px; }
    .news-hero { padding:22px 18px; }
    .news-title { font-size:1.4rem; }
    .news-grid { grid-template-columns:1fr; }
    .create-news-btn { width:100%; }
    .news-card-body { padding:16px; }
}
</style>

@endsection