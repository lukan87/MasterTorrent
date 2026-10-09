<header class="contact-hero">
    <span class="contact-hero-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
    <div class="contact-hero-content">
        <span class="contact-eyebrow">{{ $eyebrow }}</span>
        <h1>{{ $heading }}</h1>
        <p>{{ $description }}</p>
        @if(isset($actionUrl))
            <a class="contact-hero-link" href="{{ $actionUrl }}">{{ $actionLabel }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        @endif
    </div>
</header>
