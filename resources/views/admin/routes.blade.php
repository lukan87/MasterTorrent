{{-- resources/views/admin/routes.blade.php --}}
@extends('layouts.app')

@section('content')

<div class="container-fluid px-3 px-md-4 py-3 admin-routes-page">

    @php
        /*
         * Group routes by the first part of their route name.
         * Example:
         * forum.index       -> Forum
         * forum.categories  -> Forum
         * admin.users.index -> Admin
         * torrents.show     -> Torrents
         *
         * Unnamed routes are placed in "Other Routes".
         */
        $routeGroups = collect($routes)
            ->sortBy(function ($route) {
                return strtolower($route->getName() ?? '');
            })
            ->groupBy(function ($route) {
                $name = $route->getName();

                if (!$name) {
                    return 'Other Routes';
                }

                return ucfirst(strtolower(explode('.', $name)[0]));
            })
            ->sortKeys();
    @endphp


    <div class="routes-panel">

        <!-- HEADER -->
        <div class="routes-header">

            <div class="routes-heading">
                <div class="routes-icon">
                    <i class="bi bi-diagram-3"></i>
                </div>

                <div>
                    <h2>All Routes</h2>
                    <p>Routes are organised by name for easier navigation.</p>
                </div>
            </div>

            <div class="routes-count">
                <i class="bi bi-list-ul"></i>
                {{ count($routes) }} Routes
            </div>

        </div>


        <!-- ROUTE GROUPS -->
        <div class="routes-groups">

            @forelse ($routeGroups as $groupName => $groupRoutes)

                <div class="route-group">

                    <button
                        class="route-group-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#route-group-{{ \Illuminate\Support\Str::slug($groupName) }}"
                        aria-expanded="false"
                        aria-controls="route-group-{{ \Illuminate\Support\Str::slug($groupName) }}"
                    >

                        <span class="route-group-left">

                            <span class="route-group-icon">
                                <i class="bi bi-folder2"></i>
                            </span>

                            <span class="route-group-name">
                                {{ $groupName }}
                            </span>

                            <span class="route-group-count">
                                {{ $groupRoutes->count() }}
                            </span>

                        </span>

                        <i class="bi bi-chevron-down route-chevron"></i>

                    </button>


                    <div
                        id="route-group-{{ \Illuminate\Support\Str::slug($groupName) }}"
                        class="collapse"
                    >

                        <div class="route-group-body">

                            <div class="table-responsive">

                                <table class="table routes-table align-middle mb-0">

                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>URI</th>
                                            <th>Method</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach ($groupRoutes as $route)

                                            <tr>

                                                <td>
                                                    <span class="route-name">
                                                        {{ $route->getName() ?? 'Unnamed Route' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <code class="route-uri">
                                                        {{ $route->uri() }}
                                                    </code>
                                                </td>

                                                <td>

                                                    <div class="route-methods">

                                                        @foreach ($route->methods() as $method)

                                                            <span class="method-badge method-{{ strtolower($method) }}">
                                                                {{ $method }}
                                                            </span>

                                                        @endforeach

                                                    </div>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="routes-empty">
                    <i class="bi bi-diagram-3"></i>
                    <span>No routes found.</span>
                </div>

            @endforelse

        </div>

    </div>

</div>


<style>
/* =========================================
   FILEIPLAY ADMIN ROUTES
   Organised by route name
   Dark glass + teal forum style
========================================= */

.admin-routes-page {
    max-width: 1550px;
}

.routes-panel {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .85rem;
    box-shadow: 0 10px 28px rgba(0,0,0,.22);
}

.routes-panel::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--ui-accent, #22d3c5);
}

/* HEADER */

.routes-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .85rem 1rem;
    background: rgba(15,23,42,.4);
    border-bottom: 1px solid var(--ui-border, rgba(148,163,184,.16));
}

.routes-heading {
    display: flex;
    align-items: center;
    gap: .7rem;
    min-width: 0;
}

.routes-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.16);
    border-radius: .6rem;
    font-size: 17px;
}

.routes-heading h2 {
    margin: 0;
    color: #f1f5f9;
    font-size: 14px;
    font-weight: 700;
}

.routes-heading p {
    margin: .15rem 0 0;
    color: #64748b;
    font-size: 11px;
}

.routes-count {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .35rem .55rem;
    color: #67e8f9;
    background: rgba(14,116,144,.18);
    border: 1px solid rgba(34,211,238,.2);
    border-radius: .4rem;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

/* GROUPS */

.routes-groups {
    padding: .65rem;
}

.route-group {
    margin-bottom: .45rem;
    overflow: hidden;
    background: rgba(15,23,42,.38);
    border: 1px solid rgba(148,163,184,.12);
    border-radius: .6rem;
}

.route-group:last-child {
    margin-bottom: 0;
}

.route-group-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .65rem .7rem;
    color: #cbd5e1;
    background: transparent;
    border: 0;
    text-align: left;
    cursor: pointer;
    transition: background .15s ease, color .15s ease;
}

.route-group-toggle:hover,
.route-group-toggle[aria-expanded="true"] {
    color: #f1f5f9;
    background: rgba(34,211,197,.045);
}

.route-group-left {
    display: flex;
    align-items: center;
    gap: .55rem;
    min-width: 0;
}

.route-group-icon {
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.14);
    border-radius: .45rem;
    font-size: 13px;
}

.route-group-name {
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 700;
}

.route-group-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    padding: .15rem .35rem;
    color: #67e8f9;
    background: rgba(14,116,144,.18);
    border: 1px solid rgba(34,211,238,.15);
    border-radius: .3rem;
    font-size: 10px;
    font-weight: 700;
}

.route-chevron {
    color: #64748b;
    font-size: 12px;
    transition: transform .18s ease;
}

.route-group-toggle[aria-expanded="true"] .route-chevron {
    transform: rotate(180deg);
    color: var(--ui-accent, #22d3c5);
}

.route-group-body {
    border-top: 1px solid rgba(148,163,184,.1);
}

/* TABLE */

.routes-table {
    color: #cbd5e1;
    font-size: 13px;
}

.routes-table thead th {
    padding: .55rem .6rem;
    color: #64748b;
    background: rgba(15,23,42,.3);
    border-bottom: 1px solid rgba(148,163,184,.1);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .45px;
    white-space: nowrap;
}

.routes-table tbody td {
    padding: .5rem .6rem;
    color: #cbd5e1;
    background: transparent;
    border-bottom: 1px solid rgba(148,163,184,.07);
    vertical-align: middle;
}

.routes-table tbody tr:last-child td {
    border-bottom: 0;
}

.routes-table tbody tr {
    transition: background .15s ease;
}

.routes-table tbody tr:hover td {
    background: rgba(34,211,197,.035);
}

.route-name {
    color: #e2e8f0;
    font-size: 12px;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.route-uri {
    display: inline-block;
    padding: .18rem .35rem;
    color: #67e8f9;
    background: rgba(15,23,42,.5);
    border: 1px solid rgba(34,211,197,.1);
    border-radius: .3rem;
    font-family: var(--bs-font-monospace);
    font-size: 11px;
    overflow-wrap: anywhere;
}

.route-methods {
    display: flex;
    flex-wrap: wrap;
    gap: .25rem;
}

.method-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    padding: .2rem .4rem;
    color: #cbd5e1;
    background: rgba(51,65,85,.42);
    border: 1px solid rgba(148,163,184,.14);
    border-radius: .35rem;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .25px;
}

.method-get {
    color: #67e8f9;
    background: rgba(14,116,144,.18);
    border-color: rgba(34,211,238,.18);
}

.method-post {
    color: #bbf7d0;
    background: rgba(20,83,45,.22);
    border-color: rgba(74,222,128,.18);
}

.method-put,
.method-patch {
    color: #fde68a;
    background: rgba(120,53,15,.22);
    border-color: rgba(251,191,36,.18);
}

.method-delete {
    color: #fecaca;
    background: rgba(127,29,29,.25);
    border-color: rgba(248,113,113,.18);
}

.method-options,
.method-head {
    color: #c4b5fd;
    background: rgba(76,29,149,.18);
    border-color: rgba(167,139,250,.16);
}

/* EMPTY */

.routes-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: 2rem 1rem;
    color: #64748b;
    font-size: 13px;
}

.routes-empty i {
    color: var(--ui-accent, #22d3c5);
    font-size: 20px;
}

/* MOBILE */

@media (max-width: 767.98px) {
    .admin-routes-page {
        padding-top: .75rem !important;
        padding-bottom: .75rem !important;
    }

    .routes-header {
        align-items: flex-start;
    }

    .routes-table {
        min-width: 720px;
    }
}

@media (max-width: 480px) {
    .routes-header {
        flex-direction: column;
        gap: .65rem;
    }

    .routes-count {
        align-self: flex-start;
    }

    .route-group-toggle {
        padding: .6rem;
    }

    .route-group-name {
        font-size: 12px;
    }
}
</style>

@endsection
