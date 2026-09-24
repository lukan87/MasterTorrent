<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FileIplay')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="lukan87">
    <meta name="description" content="FileIplay is a torrent tracker for movies, TV series, music, games, software, documentaries and more. Discover and browse torrents in one place.">
    <link rel="canonical" href="https://fileiplay.org/">
   <!-- Favicon link -->
   <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kode+Mono:wght@400..700&family=Overpass:ital,wght@0,100..900;1,100..900&family=Titillium+Web&display=swap" rel="stylesheet">


<!--end::Primary Meta Tags--><!--begin::Fonts-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous"><!--end::Fonts--><!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css" integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous"><!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous"><!--end::Third Party Plugin(Bootstrap Icons)--><!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.css') }}"><!--end::Required Plugin(AdminLTE)--><!-- apexcharts -->
    <link rel="stylesheet" href="{{ asset('css/lity/litty.css') }}">
    <link href="{{ asset('css/custom.css') }}?v={{ time() }}" rel="stylesheet">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">


<!-- SweetAlert CSS -->
<link rel="stylesheet" href="{{ asset('css/sweet/sweet.css') }}">

<script src="{{ asset('js/sweet/sweet.js') }}"></script>
<script>
    window.App = {
        routes: {
            sendToSeedbox: "{{ route('torrents.sendToSeedbox', ':id') }}"
        }
    };
</script>


@auth
@php
    $profileBackground = auth()->user()->background
        ? auth()->user()->background
        : asset('images/default-profile-bg.jpg');
@endphp

<style>

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

.content-overlay {
    position: relative;
    z-index: 1;
    background: none;
    padding: 20px;
}

html::before {
    content: '';
    position: fixed;
    top: 53px;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: linear-gradient(to bottom, rgba(117, 98, 98, 0.18), rgba(78, 63, 63, 0.48)), url('{{ $profileBackground }}');
    background-position: center top;
    background-size: cover;
    background-repeat: no-repeat;
    opacity: 0.7;
    z-index: -1;
}

html::after {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.05) 0%,
        rgba(0,0,0,0.25) 25%,
        rgba(0,0,0,0.55) 55%,
        rgba(0,0,0,0.85) 80%,
        rgba(0,0,0,1) 100%
    );
    pointer-events: none;
    z-index: -1;
}

</style>
@endauth

<style>
    /* Make sidebar scrollable on mobile */
.sidebar-wrapper {
    max-height: 90vh; /* Full height of the viewport */
    overflow-y: auto;
}

@media (max-width: 768px) {
    .sidebar-wrapper {
        max-height: 80vh; /* Adjust as needed */
    }
}
html {
        font-size: 13px;
    }
</style>

<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
document.addEventListener("click", function(e){

    if(!e.target.classList.contains("bbcode-image")) return;

    const overlay = document.createElement("div");
    overlay.style.position = "fixed";
    overlay.style.top = 0;
    overlay.style.left = 0;
    overlay.style.width = "100%";
    overlay.style.height = "100%";
    overlay.style.background = "rgba(0,0,0,0.9)";
    overlay.style.display = "flex";
    overlay.style.alignItems = "center";
    overlay.style.justifyContent = "center";
    overlay.style.zIndex = 9999;

    const img = document.createElement("img");
    img.src = e.target.src;
    img.style.maxWidth = "95%";
    img.style.maxHeight = "95%";
    img.style.borderRadius = "10px";

    overlay.appendChild(img);

    overlay.addEventListener("click", () => overlay.remove());

    document.body.appendChild(overlay);

});
</script>

<script>

document.addEventListener("DOMContentLoaded", function(){

let images = [];
let currentIndex = 0;
let overlay = null;

function openLightbox(index){

    images = Array.from(document.querySelectorAll(".torrent-description img"));
    currentIndex = index;

    overlay = document.createElement("div");
    overlay.className = "lightbox-overlay";

    const img = document.createElement("img");
    img.className = "lightbox-image";
    img.src = images[currentIndex].src;

    overlay.appendChild(img);
    document.body.appendChild(overlay);

    updateImage();

}

function updateImage(){
    const img = overlay.querySelector("img");
    img.src = images[currentIndex].src;
}

function next(){
    currentIndex = (currentIndex + 1) % images.length;
    updateImage();
}

function prev(){
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    updateImage();
}

document.addEventListener("click", function(e){

    if(e.target.closest(".torrent-description img")){

        const imgs = Array.from(document.querySelectorAll(".torrent-description img"));
        const index = imgs.indexOf(e.target);

        openLightbox(index);

    }

    if(e.target.classList.contains("lightbox-overlay")){
        e.target.remove();
    }

});

document.addEventListener("keydown", function(e){

    if(!overlay) return;

    if(e.key === "Escape"){
        overlay.remove();
        overlay = null;
    }

    if(e.key === "ArrowRight"){
        next();
    }

    if(e.key === "ArrowLeft"){
        prev();
    }

});

let startX = 0;

document.addEventListener("touchstart", function(e){
    startX = e.changedTouches[0].screenX;
});

document.addEventListener("touchend", function(e){

    if(!overlay) return;

    let endX = e.changedTouches[0].screenX;

    if(endX - startX > 50){
        prev();
    }

    if(startX - endX > 50){
        next();
    }

});

});

</script>



</head>
