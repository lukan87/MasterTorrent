<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Last Files')</title>
    <meta name="author" content="lukan87">
   <!-- Favicon link -->
   <link rel="shortcut icon" href="{{ secure_asset('favicon.ico') }}" type="image/x-icon">

<!--end::Primary Meta Tags--><!--begin::Fonts-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous"><!--end::Fonts--><!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css" integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous"><!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous"><!--end::Third Party Plugin(Bootstrap Icons)--><!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ secure_asset('dist/css/adminlte.css') }}"><!--end::Required Plugin(AdminLTE)--><!-- apexcharts -->
    <link rel="stylesheet" href="{{ secure_asset('css/lity/litty.css') }}">
    <link href="{{ asset('css/custom.css') }}?v={{ time() }}" rel="stylesheet">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">


<!-- SweetAlert CSS -->
<link rel="stylesheet" href="{{ secure_asset('css/sweet/sweet.css') }}">

<script src="{{ secure_asset('js/sweet/sweet.js') }}"></script>


<style>
    /* Make sidebar scrollable on mobile */
.sidebar-wrapper {
    max-height: 100vh; /* Full height of the viewport */
    overflow-y: auto;
}

@media (max-width: 768px) {
    .sidebar-wrapper {
        max-height: 80vh; /* Adjust as needed */
    }
}
</style>


</head>
