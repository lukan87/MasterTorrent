@props([
    'title' => '',
    'icon' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4']) }}>
    <div class="d-flex align-items-center gap-3 min-w-0">
        @if($icon)
            <span class="page-heading-icon" aria-hidden="true">
                <i class="bi {{ $icon }}"></i>
            </span>
        @endif

        <div class="min-w-0">
            <h1 class="page-heading-title">{{ $title }}</h1>
            @if($subtitle)
                <p class="page-heading-subtitle mb-0">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if(isset($actions) && !empty($actions))
        <div class="page-heading-actions d-flex flex-wrap align-items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>