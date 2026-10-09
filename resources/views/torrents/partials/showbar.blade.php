
<div class="showbar-card modern-showbar mb-4 mt-4" data-torrent-showbar>

    {{-- HEADER --}}
    <div class="card-header modern-showbar-header">

        {{-- LEFT --}}
        <div class="d-flex align-items-center gap-3 flex-wrap">

            <div class="torrent-icon-box">
                <i class="bi bi-collection-play-fill"></i>
            </div>

            <div>

                <h4 class="modern-torrent-title mb-1">
                    {{ $torrent->name }}
                </h4>

                <div class="modern-subinfo">

                    <span class="modern-badge torrent-release-badge torrent-created-badge"
                          data-bs-toggle="tooltip"
                          title="Created: {{ $torrent->created_at->format('M d, Y') }}"
                          aria-label="Created: {{ $torrent->created_at->format('M d, Y') }}">
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                        {{ $torrent->created_at->format('M d, Y') }}
                    </span>

                    <span class="modern-badge torrent-release-badge torrent-uploader-badge"
                          data-bs-toggle="tooltip"
                          title="Uploader: {{ $torrent->uploaderLabel() }}"
                          aria-label="Uploader: {{ $torrent->uploaderLabel() }}">
                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                        {{ $torrent->uploaderLabel() }}
                    </span>

                    @php
                        $releaseDetails = \App\Helpers\TorrentReleaseParser::parse($torrent->name);
                        $releaseLabels = [
                            'resolution' => ['Resolution', 'bi-display'],
                            'source' => ['Source', 'bi-film'],
                            'audio' => ['Audio', 'bi-volume-up'],
                            'service' => ['Streaming service', 'bi-tv'],
                            'group' => ['Release group', 'bi-people'],
                        ];
                    @endphp

                    @foreach ($releaseLabels as $key => [$label, $icon])
                        @if ($releaseDetails[$key] !== null)
                            <span class="modern-badge torrent-release-badge torrent-release-{{ $key }}"
                                  data-bs-toggle="tooltip"
                                  title="{{ $label }}: {{ $releaseDetails[$key] }}"
                                  aria-label="{{ $label }}: {{ $releaseDetails[$key] }}">
                                <i class="bi {{ $icon }}" aria-hidden="true"></i>
                                {{ $releaseDetails[$key] }}
                            </span>
                        @endif
                    @endforeach

                </div>

            </div>

        </div>

        {{-- TAGS --}}
        <div class="modern-tags-wrap ms-md-auto">

            @if($torrent->free)
                <span class="modern-badge free-badge"
                      data-bs-toggle="tooltip"
                      title="This torrent is freeleech — downloading won't reduce your ratio.">

                    <i class="bi bi-lightning-fill"></i>
                    FREE

                </span>
            @endif

            @if($torrent->double)
                <span class="modern-badge double-badge"
                      data-bs-toggle="tooltip"
                      title="Double upload credit — seeding counts double toward your ratio.">

                    <i class="bi bi-chevron-double-up"></i>
                    DOUBLE

                </span>
            @endif

            @if($torrent->seedbox)
                <span class="modern-badge seedbox-badge"
                      data-bs-toggle="tooltip"
                      title="This torrent is hosted on a seedbox — expect high seed speed.">

                    <i class="bi bi-cloud-fill"></i>
                    SEEDBOX

                </span>
            @endif

            @if($torrent->sticky)
                <span class="modern-badge sticky-badge"
                      data-bs-toggle="tooltip"
                      title="Sticky torrent — always pinned to the top of all torrents.">

                    <i class="bi bi-pin-angle-fill"></i>
                    STICKY

                </span>
            @endif

            <a href="{{ route('tickets.create', ['torrent_id' => $torrent->id]) }}"
               class="modern-badge report-badge-modern text-decoration-none"
               data-bs-toggle="tooltip"
               title="Report a problem with this torrent">

                <i class="bi bi-flag-fill"></i>
                REPORT

            </a>

        </div>

    </div>

    {{-- BODY --}}
    <div class="card-body modern-showbar-body">

        {{-- LEFT ACTIONS --}}
        <div class="d-flex flex-wrap align-items-center gap-2">

            @if (Auth::check() && Auth::user()->hit_and_run_count > 20)

                <div class="modern-alert-danger">

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    Download restricted: more than 20 H&R.

                </div>

            @else

                @if (Auth::check())

                    @php
                        $slots = Auth::user()->slots;
                        $hasSeedboxes = $userSeedboxes->isNotEmpty();
                    @endphp

                    @if($slots > 0 || $hasSeedboxes)

@include('partials._watch-online-btn', ['watchUrl' => $watchUrl ?? null])
                        <div class="btn-group">

                            <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"
                               class="btn modern-download-btn">

                                <i class="bi bi-download me-1"></i>

                                Download

                            </a>

                            <button type="button"
                                    class="btn modern-download-btn dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown">
                            </button>

                            <ul class="dropdown-menu dropdown-menu-dark modern-dropdown-menu shadow-lg">

                                {{-- FREE --}}
                                @if($slots > 0)

                                    <li>

                                        <a class="dropdown-item modern-dropdown-item"
                                           href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}?free=1">

                                            <i class="bi bi-lightning-fill me-2"></i>

                                            Free Download

                                        </a>

                                    </li>

                                    <li>

                                        <a class="dropdown-item modern-dropdown-item"
                                           href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}?double=1">

                                            <i class="bi bi-chevron-double-up me-2"></i>

                                            Double Upload

                                        </a>

                                    </li>

                                @endif

                                {{-- SEEDBOXES --}}
                                @if($hasSeedboxes)

    <li>
        <hr class="dropdown-divider">
    </li>

    <li class="dropdown-header text-info">

        <i class="bi bi-cloud-arrow-up-fill me-1"></i>
        Send to Seedbox

    </li>

    @foreach($userSeedboxes as $seedbox)

        <li>

            <form action="{{ route('torrents.sendToSeedbox', $torrent) }}"
                  method="POST"
                  class="m-0 p-0">

                @csrf

                <input type="hidden"
                       name="seedbox_id"
                       value="{{ $seedbox->id }}">

                <button type="submit"
                        class="dropdown-item modern-dropdown-item">

                    <i class="bi bi-hdd-network-fill me-2"></i>

                    {{ $seedbox->name }}

                </button>

            </form>

        </li>

    @endforeach

@endif

                            </ul>

                        </div>

                    @else

                        <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"
                           class="btn modern-download-btn">

                            <i class="bi bi-download me-1"></i>

                            Download

                        </a>

                    @endif

                @endif

            @endif

            {{-- SUBTITLE --}}
@php
    $allowedCategoryIds = [1, 5, 9, 11, 13, 18, 20, 24, 31, 54, 56, 82];
    $uploadSubtitleModalId = 'uploadSubtitleModal-' . $torrent->id;
@endphp

@if(in_array($torrent->category_id, $allowedCategoryIds))

    <button type="button"
            class="btn modern-action-btn"
            data-bs-toggle="modal"
            data-bs-target="#{{ $uploadSubtitleModalId }}">

        <i class="bi bi-badge-cc-fill me-1"></i>
        Subtitle

    </button>

@endif

            {{-- EDIT --}}
            @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR || Auth::id() === $torrent->owner))

                <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"
                   class="btn modern-action-btn info-btn"
                   data-bs-toggle="tooltip"
                   title="Edit Torrent">

                    <i class="bi bi-pencil-square"></i>

                    Edit

                </a>

            @endif

            {{-- BUMP --}}
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                @php
                    $recentlyUploaded = $torrent->created_at->gt(now()->subDays(15));
                    $recentlyBumped = $torrent->bumped_at && $torrent->bumped_at->gt(now()->subDays(30));
                @endphp

                @if (!$recentlyUploaded)

                    @if ($recentlyBumped)

                        <button class="btn modern-action-btn disabled opacity-75"
                                disabled
                                data-bs-toggle="tooltip"
                                title="Can be bumped again on {{ $torrent->bumped_at->copy()->addDays(30)->format('M d, Y') }}">

                            <i class="bi bi-x-circle"></i>

                            Bumped

                        </button>

                    @else

                        <form action="{{ route('torrents.bump', $torrent->id) }}"
                              method="POST">

                            @csrf

                            <button type="submit"
                                    class="btn modern-action-btn success-btn"
                                    data-bs-toggle="tooltip"
                                    title="Bump Torrent">

                                <i class="bi bi-arrow-up-circle"></i>

                                Bump

                            </button>

                        </form>

                    @endif

                @endif

            @endif

            {{-- REACTIONS --}}
            @php
                $activeReaction = $userReaction ? $userReaction->reaction : '👍';
                $totalReactions = $reactions->count();

                // Prepare tooltip text: group by reaction, list names
                $tooltipContent = $reactions->groupBy('reaction')->map(function ($items, $reaction) {
                    $names = $items->pluck('user.name')->take(3)->join(', ');
                    if($items->count() > 3) $names .= '...';
                    return "<strong>$reaction</strong>: $names";
                })->join('<br>');
            @endphp

            <span class="d-inline-block" id="reaction-tooltip-wrap" data-bs-toggle="tooltip" data-bs-html="true" title="{!! $tooltipContent !!}">
                <div class="dropdown reaction-dropdown">
                    <button class="btn modern-action-btn dropdown-toggle" type="button" id="reactionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span id="active-reaction">{{ $activeReaction }}</span> <span class="badge" id="total-reactions">{{ $totalReactions }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark p-2" aria-labelledby="reactionDropdown">
                    <li class="d-flex gap-1">
                        @foreach(['👍', '😂', '😮', '😢', '😎', '💖', '🥱', '😤'] as $r)
                            <button type="button"
                                    class="btn btn-sm btn-reaction reaction-btn {{ ($userReaction && $userReaction->reaction === $r) ? 'active' : '' }}"
                                    data-reaction="{{ $r }}"
                                    data-torrent-id="{{ $torrent->id }}"
                                    title="{{ $r }}">
                                {{ $r }}
                            </button>
                        @endforeach
                    </li>
                </ul>
            </div>
            </span>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Tooltip
    |--------------------------------------------------------------------------
    */

    const tooltipElement = document.getElementById('reaction-tooltip-wrap');

    let reactionTooltip = null;

    if (tooltipElement) {
        reactionTooltip = new bootstrap.Tooltip(tooltipElement, {
            html: true,
            placement: 'top',
            trigger: 'hover',
            container: 'body'
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Build Tooltip
    |--------------------------------------------------------------------------
    */

    function buildReactionTooltip(tooltipData) {

        if (!tooltipData || tooltipData.length === 0) {
            return 'No reactions yet';
        }

        return tooltipData.map(item => {

            let users = item.users || [];

            let names = users.join(', ');

            if (item.count > 3) {
                names += '...';
            }

            return `
                <div class="reaction-tooltip-row">
                    <span class="reaction-tooltip-emoji">
                        ${item.reaction}
                    </span>

                    <strong>${item.count}</strong>

                    <span class="reaction-tooltip-users">
                        ${names}
                    </span>
                </div>
            `;

        }).join('');
    }


    /*
    |--------------------------------------------------------------------------
    | Update Tooltip
    |--------------------------------------------------------------------------
    */

    function updateReactionTooltip(tooltipData) {

        if (!tooltipElement) {
            return;
        }

        const newContent = buildReactionTooltip(tooltipData);

        /*
         * Dispose of the old Bootstrap tooltip.
         */
        if (reactionTooltip) {
            reactionTooltip.dispose();
        }

        /*
         * Update the title and Bootstrap data attribute.
         */
        tooltipElement.setAttribute('title', newContent);
        tooltipElement.setAttribute('data-bs-original-title', newContent);

        /*
         * Create a fresh tooltip.
         */
        reactionTooltip = new bootstrap.Tooltip(tooltipElement, {
            html: true,
            placement: 'top',
            trigger: 'hover',
            container: 'body'
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Reaction Buttons
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.reaction-btn').forEach(btn => {

        btn.addEventListener('click', function () {

            const reaction = this.dataset.reaction;

            fetch(`{{ route('torrents.react', $torrent->id) }}`, {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },

                body: JSON.stringify({
                    reaction: reaction
                })

            })

            .then(async response => {

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(
                        data.message || 'Something went wrong.'
                    );
                }

                return data;

            })

            .then(data => {

                if (!data.success) {

                    showReactionToast(
                        data.message || 'Something went wrong.',
                        'error'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Update Active Reaction
                |--------------------------------------------------------------------------
                */

                document.getElementById('active-reaction').innerText =
                    data.activeReaction;


                /*
                |--------------------------------------------------------------------------
                | Update Total Reaction Count
                |--------------------------------------------------------------------------
                */

                document.getElementById('total-reactions').innerText =
                    data.totalReactions;


                /*
                |--------------------------------------------------------------------------
                | Update Active Reaction Button
                |--------------------------------------------------------------------------
                */

                document.querySelectorAll('.reaction-btn').forEach(button => {

                    button.classList.remove('active');

                    if (
                        data.activeReaction &&
                        button.dataset.reaction === data.activeReaction
                    ) {
                        button.classList.add('active');
                    }

                });


                /*
                |--------------------------------------------------------------------------
                | Update Tooltip
                |--------------------------------------------------------------------------
                */

                updateReactionTooltip(data.tooltip);


                /*
                |--------------------------------------------------------------------------
                | Success Toast
                |--------------------------------------------------------------------------
                */

                showReactionToast(
                    'Reaction updated successfully.',
                    'success'
                );

            })

            .catch(error => {

                showReactionToast(
                    error.message || 'Something went wrong.',
                    'error'
                );

            });

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Toast
    |--------------------------------------------------------------------------
    */

    window.showReactionToast = function (message, type = 'success') {

        const existingToast =
            document.getElementById('reaction-toast');

        if (existingToast) {
            existingToast.remove();
        }


        const toast = document.createElement('div');

        toast.id = 'reaction-toast';

        toast.className =
            `reaction-toast reaction-toast-${type}`;


        toast.innerHTML = `
            <div class="reaction-toast-icon">
                ${type === 'success' ? '✓' : '⚠'}
            </div>

            <div class="reaction-toast-message">
                ${message}
            </div>

            <button type="button"
                    class="reaction-toast-close"
                    aria-label="Close">
                &times;
            </button>
        `;


        document.body.appendChild(toast);


        /*
        |--------------------------------------------------------------------------
        | Close button
        |--------------------------------------------------------------------------
        */

        toast.querySelector('.reaction-toast-close')
            .addEventListener('click', function () {

                toast.classList.remove('show');

                setTimeout(() => {
                    toast.remove();
                }, 300);

            });


        /*
        |--------------------------------------------------------------------------
        | Animate in
        |--------------------------------------------------------------------------
        */

        setTimeout(() => {
            toast.classList.add('show');
        }, 10);


        /*
        |--------------------------------------------------------------------------
        | Automatically close
        |--------------------------------------------------------------------------
        */

        setTimeout(() => {

            if (!toast.isConnected) {
                return;
            }

            toast.classList.remove('show');

            setTimeout(() => {

                if (toast.isConnected) {
                    toast.remove();
                }

            }, 300);

        }, 3000);
    };

});
</script>

            <div data-torrent-subscription class="d-flex align-items-center gap-2 flex-wrap" aria-live="polite" aria-busy="false">
                @include('torrents.partials.subscription')
            </div>

        </div>
        <div class="modern-stats-wrap ms-md-auto">

            <span class="modern-stat-badge category-badge"
                  data-bs-toggle="tooltip"
                  title="Category">

                <i class="bi bi-tags-fill"></i>

                {{ $torrent->category->name }}

            </span>

            @if(!empty($fileTree) || ($torrent->files_count ?? 0) > 0)

                <button type="button"
                        class="modern-stat-badge files-badge border-0"
                        data-bs-toggle="modal"
                        data-bs-target="#torrentFilesModal"
                        title="View torrent files">

                    <i class="bi bi-folder2-open"></i>

                    {{ $torrent->files_count ?? $torrent->files->count() }} Files

                </button>

            @endif

            {{-- SEEDERS --}}
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

                <a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?seeders"
                   class="modern-stat-badge seeders-badge text-decoration-none">

                    <i class="bi bi-cloud-arrow-up-fill"></i>

                    {{ $torrent->seeders }}

                </a>

            @else

                <span class="modern-stat-badge seeders-badge">

                    <i class="bi bi-cloud-arrow-up-fill"></i>

                    {{ $torrent->seeders }}

                </span>

            @endif

            {{-- LEECHERS --}}
            @if($torrent->leechers > 0)

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

                    <a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?leechers"
                       class="modern-stat-badge leechers-badge text-decoration-none">

                        <i class="bi bi-cloud-arrow-down-fill"></i>

                        {{ $torrent->leechers }}

                    </a>

                @else

                    <span class="modern-stat-badge leechers-badge">

                        <i class="bi bi-cloud-arrow-down-fill"></i>

                        {{ $torrent->leechers }}

                    </span>

                @endif

            @endif

            {{-- COMPLETED --}}
            @if(auth()->check() && auth()->user()->user_class >= \App\Models\UserClass::MODERATOR)
                <a href="{{ route('torrents.completed', ['id' => $torrent->id]) }}"
                   class="modern-stat-badge completed-badge text-decoration-none"
                   title="Times completed — view completed downloads"
                   aria-label="{{ $torrent->times_completed }} times completed. View completed downloads">
                    <i class="bi bi-download"></i>
                    {{ $torrent->times_completed }}
                </a>
            @else
                <span class="modern-stat-badge completed-badge" title="Times completed">
                    <i class="bi bi-download"></i>
                    {{ $torrent->times_completed }}
                </span>
            @endif

            {{-- SIZE --}}
            <span class="modern-stat-badge size-badge">

                <i class="bi bi-pie-chart-fill"></i>

                {{ \App\Helpers\FormatHelper::formatSize($torrent->size) }}

            </span>

        </div>

    </div>

</div>

@include('torrents.partials.subtitles')


<style>
.modern-showbar{position:relative;width:100%;max-width:100%;background:linear-gradient(135deg,var(--theme-surface, rgba(14,21,33,.95)),var(--theme-surface, rgba(10,15,27,.84)));border:1px solid var(--ui-border);border-left:3px solid var(--ui-accent);border-radius:.9rem;overflow:visible;backdrop-filter:blur(14px);box-shadow:0 10px 30px var(--theme-shadow, rgba(0,0,0,.22));color:var(--theme-text, #e6edf3);z-index:1}
.modern-showbar::before{display:none}
.modern-showbar-header{padding:16px 18px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;background:transparent;border-bottom:1px solid var(--ui-border)}
.torrent-icon-box{width:48px;height:48px;border-radius:.7rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:var(--theme-teal-soft, rgba(45,212,191,.10));border:1px solid var(--theme-teal-border, rgba(45,212,191,.22));color:var(--ui-accent);font-size:1.2rem;box-shadow:none}
.modern-torrent-title{margin:0;font-size:var(--site-font-body, 13px);line-height:1.4;font-weight:700;color:var(--theme-text, #f1f5f9);overflow-wrap:anywhere;word-break:break-word}
.modern-subinfo{display:flex;gap:12px;flex-wrap:wrap;margin-top:5px;color:var(--theme-muted, rgba(255,255,255,.58));font-size:var(--site-font-body, 13px)}
.modern-subinfo span{display:inline-flex;align-items:center;gap:5px}.modern-subinfo i{color:var(--ui-accent)}
.modern-tags-wrap,.modern-stats-wrap{display:flex;flex-wrap:wrap;gap:7px;justify-content:flex-end;align-items:center;margin-left:auto}
.modern-badge,.modern-stat-badge{display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:6px 9px;border-radius:.55rem;font-size:var(--site-font-small, 13px);line-height:1.2;font-weight:600;border:1px solid var(--ui-border);white-space:nowrap;transition:background .15s ease,border-color .15s ease,transform .15s ease}
.modern-badge:hover,.modern-stat-badge:hover{transform:translateY(-1px)}
/* Release badges use a distinct colour for each field. */
.modern-subinfo{align-items:center;column-gap:9px;row-gap:7px}
.modern-subinfo .torrent-release-badge{padding:5px 8px;font-size:var(--site-font-small, 13px);max-width:100%;white-space:normal;overflow-wrap:anywhere}
.modern-subinfo .torrent-release-badge i{color:inherit;flex-shrink:0}
.torrent-created-badge{color:var(--theme-text, #c6d0da);background:var(--theme-surface-alt, rgba(148,163,184,.10));border-color:var(--theme-border, rgba(148,163,184,.25))}
.torrent-uploader-badge{color:var(--ui-accent);background:var(--theme-teal-soft, rgba(45,212,191,.10));border-color:var(--theme-teal-border, rgba(45,212,191,.25))}
.torrent-release-resolution{color:var(--theme-blue-text, #8fc8f5);background:var(--theme-blue-soft, rgba(59,130,246,.10));border-color:var(--theme-blue-border, rgba(59,130,246,.25))}
.torrent-release-source{color:var(--theme-green-text, #70e0a1);background:var(--theme-green-soft, rgba(34,197,94,.10));border-color:var(--theme-green-border, rgba(34,197,94,.25))}
.torrent-release-audio{color:var(--theme-blue-text, #c4b5fd);background:var(--theme-purple-soft, rgba(139,92,246,.10));border-color:var(--theme-purple-border, rgba(139,92,246,.25))}
.torrent-release-service{color:var(--theme-amber-text, #f3d46a);background:var(--theme-amber-soft, rgba(250,204,21,.10));border-color:var(--theme-amber-border, rgba(250,204,21,.25))}
.torrent-release-group{color:var(--theme-teal-text, #67dce9);background:var(--theme-teal-soft, rgba(6,182,212,.10));border-color:var(--theme-teal-border, rgba(6,182,212,.25))}
.free-badge{background:var(--theme-green-soft, rgba(34,197,94,.10));color:var(--theme-green-text, #70e0a1)}.double-badge{background:var(--theme-amber-soft, rgba(250,204,21,.10));color:var(--theme-amber-text, #f3d46a)}.seedbox-badge{background:var(--theme-teal-soft, rgba(6,182,212,.10));color:var(--theme-teal-text, #67dce9)}.sticky-badge{background:var(--theme-red-soft, rgba(239,68,68,.10));color:var(--theme-red-text, #f58b8b)}
.report-badge-modern{background:var(--theme-red-soft, rgba(239,68,68,.08));color:var(--theme-red-text, #f58b8b);text-decoration:none}.report-badge-modern:hover{background:var(--theme-red-soft, rgba(239,68,68,.14));color:var(--theme-red-text, #ffaaaa);box-shadow:none}
.modern-showbar-body{padding:16px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}
.modern-download-btn,.modern-action-btn{border:1px solid var(--ui-border);color:var(--theme-text, #dce7ef);background:var(--theme-surface-alt, rgba(255,255,255,0.0315));padding:8px 12px;border-radius:.55rem;font-size:var(--site-font-body, 13px);font-weight:600;transition:background .15s ease,border-color .15s ease,color .15s ease,transform .15s ease}
.modern-download-btn{background:var(--theme-teal-soft, rgba(45,212,191,.12));border-color:var(--theme-teal-border, rgba(45,212,191,.28));color:var(--ui-accent)}
.modern-download-btn:hover{background:var(--theme-teal-soft, rgba(45,212,191,.18));border-color:var(--theme-teal-border, rgba(45,212,191,.42));color:var(--theme-teal-text, #b8fff5);transform:translateY(-1px)}
.watch-online-btn{display:inline-flex;align-items:center;gap:4px;background:var(--theme-green-soft, rgba(34,197,94,.10));border:1px solid var(--theme-green-border, rgba(34,197,94,.28));color:var(--theme-green-text, #4ade80);padding:8px 12px;border-radius:.55rem;font-size:var(--site-font-body, 13px);font-weight:700;text-decoration:none;transition:background .15s ease,border-color .15s ease,color .15s ease,transform .15s ease}.watch-online-btn:hover{background:var(--theme-green-soft, rgba(34,197,94,.18));border-color:var(--theme-green-border, rgba(34,197,94,.42));color:var(--theme-text, #bbf7d0);transform:translateY(-1px)}.watch-online-btn i{font-size:14px}
.modern-action-btn:hover{background:var(--theme-teal-soft, rgba(45,212,191,.09));border-color:var(--theme-teal-border, rgba(45,212,191,.25));color:var(--ui-accent);transform:translateY(-1px)}
.info-btn{color:var(--theme-blue-text, #8fd5ff);background:var(--theme-blue-soft, rgba(59,130,246,.08))}.success-btn{color:var(--theme-green-text, #70e0a1);background:var(--theme-green-soft, rgba(34,197,94,.08))}.thank-btn{color:var(--theme-blue-text, #8fd5ff);background:var(--theme-blue-soft, rgba(59,130,246,.08))}.thanked-btn{color:var(--theme-green-text, #70e0a1);background:var(--theme-green-soft, rgba(34,197,94,.10))}.subscribe-btn{color:var(--theme-blue-text, #8fd5ff);background:var(--theme-blue-soft, rgba(59,130,246,.08))}.unsubscribe-btn{color:var(--theme-red-text, #ff8f8f);background:var(--theme-red-soft, rgba(239,68,68,.10))}
.modern-action-btn.disabled,.modern-action-btn:disabled{opacity:.55!important;cursor:not-allowed;transform:none!important}
.modern-dropdown-menu{min-width:250px;padding:7px;background:var(--theme-surface, rgba(10,15,27,.98));border:1px solid var(--ui-border);border-radius:.7rem;box-shadow:0 14px 35px var(--theme-shadow, rgba(0,0,0,.35))!important}
.modern-dropdown-item{padding:8px 10px;border-radius:.45rem;font-size:var(--site-font-body, 13px);color:var(--theme-text, #d8e2eb);transition:background .15s ease,color .15s ease}
.modern-dropdown-item:hover{background:var(--theme-teal-soft, rgba(45,212,191,.09));color:var(--ui-accent);transform:none}
.modern-dropdown-menu .dropdown-header{font-size:var(--site-font-small, 13px);color:var(--ui-accent)!important}.modern-dropdown-menu .dropdown-divider{border-color:var(--ui-border)}
.btn-reaction {
    font-size: 1.4rem;
    padding: 4px 8px;
    transition: transform 0.2s ease, background 0.2s ease;
    border-radius: 0.4rem;
}
.btn-reaction:hover {
    transform: scale(1.2);
    background: var(--theme-surface-alt, rgba(255,255,255,0.07));
}
.btn-reaction.active {
    background: var(--theme-teal-soft, rgba(45, 212, 191, 0.2));
    border: 1px solid var(--theme-teal-border, rgba(45, 212, 191, 0.5));
}
.modern-alert-danger{background:var(--theme-red-soft, rgba(220,38,38,.09));border:1px solid var(--theme-red-border, rgba(220,38,38,.22));color:var(--theme-red-text, #f4a0a0);padding:9px 12px;border-radius:.6rem;font-size:var(--site-font-body, 13px);font-weight:600}
.category-badge{background:var(--theme-surface-alt, rgba(148,163,184,0.056));color:var(--theme-text, #c6d0da)}.files-badge{background:var(--theme-amber-soft, rgba(250,204,21,.08));color:var(--theme-amber-text, #e8cf6d)}.seeders-badge{background:var(--theme-green-soft, rgba(34,197,94,.08));color:var(--theme-green-text, #70e0a1)}.leechers-badge{background:var(--theme-red-soft, rgba(239,68,68,.08));color:var(--theme-red-text, #f58b8b)}.completed-badge{background:var(--theme-blue-soft, rgba(59,130,246,.08));color:var(--theme-blue-text, #8fc8f5)}.size-badge{background:var(--theme-teal-soft, rgba(6,182,212,.08));color:var(--theme-teal-text, #67dce9)}
.modern-stat-badge.text-decoration-none:hover{text-decoration:none!important;border-color:var(--theme-teal-border, rgba(45,212,191,.25))}
.btn-group{position:relative}.btn-group .dropdown-menu,.dropdown-menu{z-index:999999!important}
@media(max-width:768px){
html,body{overflow-x:hidden!important}.modern-showbar{border-radius:.75rem}.modern-showbar-header{padding:13px 14px;gap:12px}.modern-showbar-body{padding:13px 14px;gap:12px}
.torrent-icon-box{width:40px;height:40px;font-size:1rem}.modern-torrent-title{font-size:var(--site-font-body, 13px);line-height:1.4;max-width:100%}.modern-subinfo{font-size:var(--site-font-small, 13px);gap:8px}
.modern-tags-wrap,.modern-stats-wrap{width:100%;margin-left:0;justify-content:flex-start;flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;padding-bottom:3px;scrollbar-width:none;-webkit-overflow-scrolling:touch}
.modern-tags-wrap::-webkit-scrollbar,.modern-stats-wrap::-webkit-scrollbar{display:none}.modern-badge,.modern-stat-badge{flex:0 0 auto;font-size:var(--site-font-small, 13px)}
.modern-showbar-body>div:first-child{width:100%;min-width:0;display:flex;flex-wrap:wrap;gap:7px}.modern-download-btn,.modern-action-btn{font-size:var(--site-font-small, 13px);padding:8px 10px}
.modern-dropdown-menu{max-width:calc(100vw - 28px)}
}
/* Subscriber count + names next to subscribe button */
.subscribers-label{display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:var(--site-font-body, 13px);color:var(--ui-text-muted);line-height:1.3;padding:2px 0}
.subscribers-label i{color:var(--ui-accent)}
.subscribers-label .subscribers-count{font-weight:700;color:var(--theme-text, #f1f5f9);white-space:nowrap}
.subscribers-label .subscribers-names{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px}
.subscribers-label .subscribers-more{color:var(--ui-accent);font-weight:700}

/* Reaction Toast */
.reaction-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;

    display: flex;
    align-items: center;
    gap: 12px;

    min-width: 280px;
    max-width: 420px;

    padding: 14px 16px;

    background: var(--theme-surface, #15181b);
    color: var(--theme-text, #fff);

    border-radius: 12px;

    box-shadow: 0 10px 35px var(--theme-shadow, rgba(0, 0, 0, 0.35));

    opacity: 0;
    transform: translateY(-15px) translateX(20px);

    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
}

.reaction-toast.show {
    opacity: 1;
    transform: translateY(0) translateX(0);
}

.reaction-toast-success {
    border-left: 4px solid var(--theme-teal-border, #20c997);
}

.reaction-toast-error {
    border-left: 4px solid var(--theme-red-border, #dc3545);
}

.reaction-toast-icon {
    width: 30px;
    height: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 50%;

    font-size: 16px;
    font-weight: bold;

    background: var(--theme-surface-alt, rgba(255,255,255,0.07));
}

.reaction-toast-success .reaction-toast-icon {
    color: var(--theme-teal-text, #20c997);
}

.reaction-toast-error .reaction-toast-icon {
    color: var(--theme-red-text, #dc3545);
}

.reaction-toast-message {
    flex: 1;

    font-size: var(--site-font-body, 13px);
    font-weight: 500;
}

.reaction-toast-close {
    border: 0;
    background: transparent;

    color: var(--theme-muted, rgba(255, 255, 255, 0.6));

    font-size: 22px;
    line-height: 1;

    cursor: pointer;

    padding: 0;
}

.reaction-toast-close:hover {
    color: var(--theme-text, #fff);
}

@media (max-width: 576px) {
    .reaction-toast {
        top: 15px;
        left: 15px;
        right: 15px;

        min-width: auto;
        max-width: none;
    }
}

/* Reaction Tooltip */
.reaction-tooltip-row {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 3px 0;
    white-space: nowrap;
}

.reaction-tooltip-emoji {
    font-size: 18px;
}

.reaction-tooltip-row strong {
    font-size: var(--site-font-small, 13px);
    opacity: 0.8;
}

.reaction-tooltip-users {
    font-size: var(--site-font-small, 13px);
    opacity: 0.9;
}
</style>
