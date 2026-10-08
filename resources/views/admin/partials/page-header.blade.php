<header class="admin-page-header">
    <div>
        <div class="admin-eyebrow">{{ $eyebrow ?? 'ADMINISTRATION' }}</div>
        <h1 class="admin-page-title">{{ $title }}</h1>
        @isset($subtitle)<p class="admin-page-subtitle">{{ $subtitle }}</p>@endisset
    </div>
    @isset($backRoute)<a href="{{ route($backRoute) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>{{ $backLabel ?? 'Back to overview' }}</a>@endisset
</header>
