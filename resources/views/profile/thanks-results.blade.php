<div class="container py-4">

    {{-- BREADCRUMB --}}
    <div class="elite-breadcrumb mb-4">

        <a href="{{ url('/') }}" class="breadcrumb-item">
            <i class="bi bi-house-door"></i>
            Home
        </a>

        <span class="breadcrumb-separator">
            <i class="bi bi-chevron-right"></i>
        </span>

        <a href="{{ route('profile.show', ['id'=>$user->id,'name'=>$user->name]) }}"
           class="breadcrumb-item">
            <i class="bi bi-person-circle"></i>
            {{ $user->name }}
        </a>

        <span class="breadcrumb-separator">
            <i class="bi bi-chevron-right"></i>
        </span>

        <span class="breadcrumb-current">
            <i class="bi bi-heart"></i>
            Thanks
        </span>

    </div>


    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">

        <div class="d-flex align-items-center gap-2">

            <h4 class="fw-semibold mb-0">
                <i class="bi bi-heart-fill me-2"></i>
                {{ $user->name }} — Thanks
            </h4>

            <span class="badge thanks-count">
                {{ $thanks->total() }}
            </span>

        </div>

        <a href="{{ route('profile.show', ['id' => $user->id,'name' => $user->name]) }}"
           class="btn btn-outline-light btn-sm elite-outline-btn">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Profile
        </a>

    </div>


    @if($thanks->isEmpty())

        <div class="elite-card empty-thanks text-center p-5">

            <div class="empty-icon">
                <i class="bi bi-heart"></i>
            </div>

            <h5>No thanks yet</h5>

            <p class="mb-0">
                Thank-you activity for this profile will appear here.
            </p>

        </div>

    @else

        <div class="row g-3">

            @foreach($thanks as $thank)

                <div class="col-12">

                    <div class="elite-card thanks-card p-4">

                        {{-- Torrent --}}
                        @if($thank->torrent)

                            <div class="thanks-torrent mb-3">

                                <i class="bi bi-film me-2"></i>

                                <a href="{{ route('torrents.show',$thank->torrent->id) }}"
                                   class="fancy-link fw-semibold">

                                    {{ $thank->torrent->name }}

                                    @if($thank->torrent->trashed())
                                        <i class="bi bi-trash text-danger ms-1"
                                           data-bs-toggle="tooltip"
                                           title="Torrent deleted"></i>
                                    @endif

                                </a>

                            </div>

                        @endif


                        {{-- Header --}}
                        <div class="d-flex align-items-start gap-3">

                            <div class="thanks-avatar">

                                @if($user->profile_image)
                                    <img src="{{ $user->profile_image }}" alt="{{ $user->name }}">
                                @else
                                    <div class="avatar-placeholder">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                @endif

                            </div>

                            <div class="flex-grow-1">

                                <div class="d-flex align-items-center gap-2 flex-wrap">

                                    <span class="thanks-label">
                                        <i class="bi bi-heart-fill me-1"></i>
                                        Thanked
                                    </span>

                                    <span class="text-muted small">
                                        • {{ $thank->created_at->diffForHumans() }}
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>


        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $thanks->links('pagination::bootstrap-5') }}
        </div>

    @endif

</div>


<style>
/* =========================================================
   FILEIPLAY — PROFILE THANKS
   Dark navy / teal forum theme
========================================================= */

.elite-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .45rem;
    padding: .65rem .85rem;
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.95)), var(--theme-surface, rgba(10,15,27,.88)));
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .65rem;
    box-shadow: 0 8px 24px var(--theme-shadow, rgba(0,0,0,.22));
    font-size: var(--site-font-body, 13px);
}

.elite-breadcrumb a {
    color: var(--theme-muted, rgba(255,255,255,.72));
    text-decoration: none;
    transition: color .2s ease;
}

.elite-breadcrumb a:hover {
    color: var(--ui-accent, var(--theme-teal-text, #2dd4bf));
}

.breadcrumb-separator {
    color: var(--theme-muted, rgba(255,255,255,.25));
    font-size: var(--site-font-small, 13px);
}

.breadcrumb-current {
    color: var(--theme-text, rgba(255,255,255,.9));
}

.elite-breadcrumb i {
    margin-right: .25rem;
}

h4 {
    color: var(--theme-text, #f1f5f9);
    font-size: 1.05rem;
}

h4 .bi {
    color: var(--ui-accent, var(--theme-teal-text, #2dd4bf)) !important;
}

.thanks-count {
    background: var(--theme-teal-soft, rgba(45,212,191,.12));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.28));
    color: var(--theme-teal-text, #8ff5e6);
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    padding: .3rem .52rem;
    border-radius: .42rem;
}

.elite-outline-btn {
    border-color: var(--theme-border, rgba(255,255,255,.14));
    color: var(--theme-muted, rgba(255,255,255,.78));
    border-radius: .5rem;
    font-size: var(--site-font-body, 13px);
    padding: .38rem .7rem;
    transition: all .2s ease;
}

.elite-outline-btn:hover,
.elite-outline-btn:focus {
    color:  var(--theme-text, #fff);
    border-color: var(--theme-teal-border, rgba(45,212,191,.5));
    background: var(--theme-teal-soft, rgba(45,212,191,.08));
    box-shadow: 0 0 0 .15rem var(--theme-shadow, rgba(45,212,191,.07));
}

.elite-card {
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.96)), var(--theme-surface, rgba(10,15,27,.9)));
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .7rem;
    box-shadow: 0 10px 28px var(--theme-shadow, rgba(0,0,0,.24));
}

.thanks-card {
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.thanks-card:hover {
    transform: translateY(-2px);
    border-color: var(--theme-teal-border, rgba(45,212,191,.22));
    box-shadow: 0 14px 34px var(--theme-shadow, rgba(0,0,0,.3));
}

.thanks-torrent {
    display: flex;
    align-items: center;
    min-width: 0;
    padding: .55rem .75rem;
    background: var(--theme-surface-alt, rgba(255,255,255,0.0175));
    border: 1px solid var(--theme-border, rgba(255,255,255,.065));
    border-radius: .5rem;
    font-size: var(--site-font-body, 13px);
}

.thanks-torrent > i {
    flex: 0 0 auto;
    color: var(--theme-amber-text, #e4b85d);
}

.thanks-torrent a {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--theme-text, #dce7f3);
    text-decoration: none;
}

.thanks-torrent a:hover {
    color: var(--ui-accent, var(--theme-teal-text, #2dd4bf));
}

.thanks-torrent .bi-trash {
    color: var(--theme-red-text, #f5a7af) !important;
}

.thanks-avatar {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
}

.thanks-avatar img,
.avatar-placeholder {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: .55rem;
    border: 1px solid var(--theme-border, rgba(255,255,255,.09));
}

.avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--theme-surface, rgba(5,9,18,.72));
    color: var(--theme-muted, rgba(255,255,255,.4));
    font-size: 1.2rem;
}

.thanks-label {
    display: inline-flex;
    align-items: center;
    padding: .3rem .5rem;
    border-radius: .4rem;
    color: var(--theme-teal-text, #8ff5e6);
    background: var(--theme-teal-soft, rgba(45,212,191,.08));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.2));
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
}

.thanks-label i {
    color: var(--theme-teal-text, #7ce7d7);
}

.thanks-card .text-muted {
    color: var(--theme-muted, rgba(203,213,225,.5)) !important;
    font-size: var(--site-font-small, 13px) !important;
}

.empty-thanks {
    color: var(--theme-muted, rgba(203,213,225,.5));
}

.empty-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: .65rem;
    background: var(--theme-teal-soft, rgba(45,212,191,.06));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.14));
    color: var(--theme-teal-text, rgba(45,212,191,.55));
    font-size: 1.2rem;
}

.empty-thanks h5 {
    color: var(--theme-muted, rgba(226,232,240,.68));
    font-size: var(--site-font-body, 13px);
}

.empty-thanks p {
    color: var(--theme-muted, rgba(203,213,225,.43));
    font-size: var(--site-font-body, 13px);
}

.pagination {
    --bs-pagination-bg: var(--theme-surface, rgba(14,21,33,.9));
    --bs-pagination-border-color: var(--theme-border, rgba(255,255,255,.08));
    --bs-pagination-color: var(--theme-muted, rgba(255,255,255,.68));
    --bs-pagination-hover-bg: var(--theme-teal-soft, rgba(45,212,191,.08));
    --bs-pagination-hover-color: var(--theme-teal-text, #8ff5e6);
    --bs-pagination-hover-border-color: var(--theme-teal-border, rgba(45,212,191,.28));
    --bs-pagination-active-bg: var(--theme-teal-soft, rgba(45,212,191,.16));
    --bs-pagination-active-border-color: var(--theme-teal-border, rgba(45,212,191,.4));
    --bs-pagination-active-color: var(--theme-teal-text, #9ff8eb);
    font-size: var(--site-font-body, 13px);
}

.pagination .page-link {
    margin: 0 .12rem;
    border-radius: .42rem;
}

@media (max-width: 768px) {
    .container.py-4 {
        padding-top: 1rem !important;
    }

    .elite-breadcrumb {
        margin-bottom: 1rem !important;
        font-size: var(--site-font-body, 13px);
        padding: .6rem .7rem;
    }

    h4 {
        font-size: var(--site-font-body, 13px);
    }

    .thanks-card {
        padding: 1rem !important;
    }

    .thanks-avatar,
    .thanks-avatar img,
    .avatar-placeholder {
        width: 40px;
        height: 40px;
    }

    .thanks-avatar {
        flex-basis: 40px;
    }

    .thanks-torrent {
        font-size: var(--site-font-body, 13px);
    }

    .thanks-torrent a {
        white-space: normal;
        overflow-wrap: anywhere;
    }
}
</style>
