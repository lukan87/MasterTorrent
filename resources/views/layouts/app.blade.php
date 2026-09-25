<!DOCTYPE html>
<html lang="en"> 

@include('layouts.partials.header')

{{-- <body class="layout-fixed sidebar-expand-lg bg-body-tertiary" data-bs-theme="dark" style="font-family: 'Titillium Web', sans-serif;">  --}}
    <body class="layout-fixed sidebar-expand-lg bg-body-tertiary" data-bs-theme="dark"> 
    <div class="app-wrapper"> 

    
     @auth

      @include('layouts.partials.navbar')
      @include('layouts.partials.sidebar')
     @endauth

        <main class="app-main"> 
             

            <div class="app-content"> 
                <div class="container-fluid"> 
                     {{-- @if(Auth::check() && Auth::user()->id == 3) --}}
                      {{-- Poll alert--}}
                        @include('layouts.partials.alerts.newpoll')

                        @if(auth()->check() && auth()->user()->user_class > 5 && !empty($waitingStaffTickets) && $waitingStaffTickets > 0)

<div class="container alert alert-danger d-flex justify-content-center align-items-center gap-2 mx-auto rounded-3 mt-5 mb-3">

    <span>
        ⚠ <strong>{{ $waitingStaffTickets }}</strong> tickets waiting for staff response.
    </span>

    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-light">
        View Tickets
    </a>

</div>

@endif

@if(auth()->check())
<div id="announcement-alert" class="container alert alert-info d-none mt-5 mb-3 text-center">
    <a href="{{ route('announcements.index') }}">
        🔔 You have new announcements
    </a>
</div>

<script>
fetch('/announcements-unread-count')
    .then(res => res.json())
    .then(data => {
        if (data.count > 0) {
            document.getElementById('announcement-alert').classList.remove('d-none');
        }
    });
</script>
@endif
                        {{-- Happy Hour alert --}}
                         @include('layouts.partials.alerts.happyhour')
                   
                     {{-- @endif --}}


                @hasSection('page-header')
                    <div class="page-header-slot mb-4">
                        @yield('page-header')
                    </div>
                @endif

                @yield('content')


           </div>


        </div> 
        </main> 
        @auth
        
<footer class="app-footer glass py-2">
    <div class="container-fluid">

    <div class="row align-items-center">

        <!-- Site name -->
        <div class="col-md-4 text-center text-md-start fs-5">
            <span class="text-info">
                <i class="bi bi-globe2 me-1"></i>
                {{ config('app.name') }}
            </span>
        </div>

        <!-- Credits -->
        <div class="col-md-4 text-center mt-2 mt-md-0">
            <small class="text-muted">
                <i class="bi bi-code-slash text-danger me-1"></i>
                Built with Laravel
                <span class="mx-2">|</span>
                <i class="bi bi-filetype-php me-1"></i>
                Developed by <strong>lukan87</strong>
            </small>
        </div>

        <!-- Browser + motto -->
        <div class="col-md-4 text-center text-md-end mt-2 mt-md-0">
            <small class="text-muted">
                <span class="me-2">Seed until you bleed</span>
                <span class="mx-2">|</span>
                Best viewed in
                <i class="bi bi-browser-chrome text-warning ms-1"></i>
                <i class="bi bi-browser-firefox text-danger ms-1"></i>
            </small>
        </div>

    </div>

    <hr class="border-secondary opacity-25 my-2">

    <!-- Copyright -->
    <div class="row">
        <div class="col-12 text-center">
            <small class="text-muted">
                © {{ date('Y') }} {{ config('app.name') }} — All rights reserved
            </small>
        </div>
    </div>

</div>


</footer>

<style>
.app-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    font-size: 0.9rem;
    margin: 0 !important;
}

/* Bootstrap-compatible: keep scrolling, hide scrollbar */
.layout-fixed .app-main {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.layout-fixed .app-main::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}

.app-footer a:hover {
    color: #4da6ff !important;
}


</style>
        @endauth
    </div> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous"></script> 
    <script src="{{ asset('dist/js/adminlte.js') }}"></script> 
 

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<script src="{{ asset('js/lity/litty.js') }}" defer></script>





<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>





<script>
window.showNotification = function(type, message) {

    const settings = {
        success: {
            title: 'Success',
            icon: '✓'
        },
        warning: {
            title: 'Warning',
            icon: '!'
        },
        info: {
            title: 'Information',
            icon: 'i'
        },
        error: {
            title: 'Error',
            icon: '×'
        }
    };

    const config = settings[type] || settings.info;

    Swal.fire({
        toast: true,
        position: 'top-end',

        html: `
            <div class="fileiplay-notification">
                
                <div class="fileiplay-notification-icon">
                    ${config.icon}
                </div>

                <div class="fileiplay-notification-content">
                    <div class="fileiplay-notification-title">
                        ${config.title}
                    </div>

                    <div class="fileiplay-notification-message">
                        ${message}
                    </div>
                </div>

            </div>
        `,

        showConfirmButton: false,
        showCloseButton: true,

        timer: 5000,
        timerProgressBar: true,

        customClass: {
            popup: `fileiplay-toast fileiplay-toast-${type}`,
            closeButton: 'fileiplay-toast-close',
            timerProgressBar: 'fileiplay-toast-progress'
        }
    });
};
</script>

@if(session('success'))
    <script>
        showNotification('success', @json(session('success')));
    </script>
@endif

@if(session('warning'))
    <script>
        showNotification('warning', @json(session('warning')));
    </script>
@endif

@if(session('info'))
    <script>
        showNotification('info', @json(session('info')));
    </script>
@endif

@if(session('error'))
    <script>
        showNotification('error', @json(session('error')));
    </script>
@endif


<!-- Back to Top Button -->
<button id="back-to-top" class="btn" type="button" aria-label="Back to top">
<i class="bi bi-arrow-up fs-4" aria-hidden="true"></i>
</button>

<script>
    // Show the "Back to Top" button when scrolling down
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#back-to-top').fadeIn();
        } else {
            $('#back-to-top').fadeOut();
        }
    });

    // Scroll to the top when the button is clicked
    $('#back-to-top').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 600);
        return false;
    });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<script>
    $(document).ready(function(){
        $(".hero-slide").owlCarousel({
            loop: true,
            margin: 10,
            nav: false,
            dots: true,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            lazyLoad: true,
        responsive:{
            0:{ items: 1 },
            576:{ items: 2 },
            768:{ items: 3 },
            1200:{ items: 4 },
            1600:{ items: 8 },
            2500:{ items: 12 }
        }
        });
    });
</script>


    @stack('scripts')

    <script>
document.addEventListener('DOMContentLoaded', function () {

    fetch('/announcements-unread-count')
        .then(res => res.json())
        .then(data => {

            let badge = document.getElementById('announcement-badge');

            if (!badge) return;

            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('d-none');
            }
        });

});
</script>


</body><!--end::Body-->

</html>
