<!DOCTYPE html>
<html lang="en" class="site-layout" data-bs-theme="dark">

@include('layouts.partials.header')

{{-- <body class="layout-fixed sidebar-expand-lg bg-body-tertiary" style="font-family: 'Titillium Web', sans-serif;">  --}}
    <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
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
<div id="announcement-alert" class="container-fluid alert alert-info d-none mt-3 mb-3 text-center">
    <a href="{{ route('announcements.index') }}">
        🔔 You have new announcements
    </a>
</div>

 {{-- <div id="latest-torrent-alert" class="container alert alert-success d-none mt-5 mb-3 text-center">
    <a href="#" id="latest-torrent-link">
        🚀 New Upload: <span id="latest-torrent-name"></span>
    </a>
</div> --}}

<script>
fetch('/announcements-unread-count', { headers: { Accept: 'application/json' } })
    .then(res => res.json())
    .then(data => {
        if (data.count > 0) {
            document.getElementById('announcement-alert').classList.remove('d-none');
        }
    });

// const fetchLatestTorrent = () => {
//     fetch('{{ route('api.latest-torrent') }}')
//         .then(res => res.json())
//         .then(data => {
//             if (data && data.name) {
//                 const alert = document.getElementById('latest-torrent-alert');
//                 const link = document.getElementById('latest-torrent-link');
//                 const name = document.getElementById('latest-torrent-name');

//                 // Only update if it's new
//                 if (name.textContent !== data.name) {
//                     link.href = data.url;
//                     name.textContent = data.name;
//                     alert.classList.remove('d-none');
//                 }
//             }
//         });
// };

// fetchLatestTorrent();
// setInterval(fetchLatestTorrent, 5000); // Check every 5 seconds
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

        <div class="row align-items-center gy-2">

            {{-- Site name --}}
            <div class="col-md-4 text-center text-md-start">
                <span class="footer-brand">
                    <i class="bi bi-globe2 text-info me-1"></i>
                    {{ config('app.name') }}
                </span>
            </div>

            {{-- Credits --}}
<div class="col-md-4 text-center mt-2 mt-md-0">
    <small class="text-muted">

        <a href="https://github.com/lukan87/MasterTorrent"
           target="_blank"
           rel="noopener noreferrer"
           class="footer-link">

            <i class="bi bi-github me-1"></i>
            Laravel {{ app()->version() }}

        </a>

        <span class="mx-2">|</span>

        <i class="bi bi-filetype-php text-primary me-1"></i>
        PHP {{ PHP_VERSION }}

        <span class="mx-2">|</span>

        <i class="bi bi-code-slash text-danger me-1"></i>
        Developed by <strong>lukan87</strong>

    </small>
</div>

            {{-- Motto / browsers --}}
            <div class="col-md-4 text-center text-md-end">
                <small class="footer-text">

                    <span class="footer-motto">
                        <i class="bi bi-arrow-repeat text-success me-1"></i>
                        Seed until you bleed
                    </span>

                    <span class="footer-divider mx-2">|</span>

                    Best viewed in

                    <i class="bi bi-browser-chrome text-warning ms-1"
                       title="Google Chrome"></i>

                    <i class="bi bi-browser-firefox text-danger ms-1"
                       title="Mozilla Firefox"></i>

                </small>
            </div>

        </div>

        <div class="footer-line"></div>

        {{-- Copyright --}}
        <div class="text-center">
            <small class="footer-copyright">
                © {{ date('Y') }} {{ config('app.name') }}
                <span class="mx-1">•</span>
                All rights reserved
            </small>
        </div>

    </div>
</footer>


<style>
.app-footer {
    margin: 0 !important;
    padding: 10px 0 !important;

    font-size: var(--site-font-body, 13px);

    border-top: 1px solid var(--theme-border, rgba(255, 255, 255, 0.08));

    background: var(--theme-surface, rgba(13,16,18,0.65));

    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}


/* Site name */
.footer-brand {
    color: var(--theme-text, #e9ecef);
    font-size: 1rem;
    font-weight: 600;
}


/* Normal footer text */
.footer-text {
    color: var(--theme-muted, #8f969d);
}


/* Laravel / GitHub link */
.footer-link {
    color: var(--theme-muted, #adb5bd);
    text-decoration: none;

    transition: color 0.2s ease;
}

.footer-link .bi-github {
    color: var(--theme-text, #dee2e6);
}

.footer-link:hover {
    color: var(--theme-teal-text, #0dcaf0);
}

.footer-link:hover .bi-github {
    color: var(--theme-text, #ffffff);
}


/* Motto */
.footer-motto {
    color: var(--theme-muted, #adb5bd);
}


/* Dividers */
.footer-divider {
    color: var(--theme-muted, rgba(255, 255, 255, 0.15));
}


/* Small separator */
.footer-line {
    width: 100%;
    height: 1px;

    margin: 8px 0 6px;

    background: linear-gradient(
        90deg,
        transparent,
        var(--theme-surface-alt, rgba(255,255,255,0.056)),
        transparent
    );
}


/* Copyright */
.footer-copyright {
    color: var(--theme-muted, #666f78);
    font-size: var(--site-font-body, 13px);
}


/* Browser icons */
.bi-browser-chrome,
.bi-browser-firefox {
    font-size: 0.95rem;
}


/* Keep scrolling but hide scrollbar */
.layout-fixed .app-main {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.layout-fixed .app-main::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}


/* Mobile */
@media (max-width: 767.98px) {

    .app-footer {
        padding: 12px 0 !important;
    }

    .footer-divider {
        margin-left: 5px !important;
        margin-right: 5px !important;
    }

    .footer-line {
        margin-top: 10px;
    }
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


<!-- =========================================================
     BACK TO TOP BUTTON
========================================================= -->
<button
    id="back-to-top"
    type="button"
    aria-label="Back to top"
    title="Back to top"
>
    <i class="bi bi-chevron-up" aria-hidden="true"></i>
</button>

<style>
    /* =========================================================
       BACK TO TOP
    ========================================================= */

    #back-to-top {
        position: fixed;
        right: 24px;
        bottom: 24px;

        width: 58px;
        height: 58px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 0;

        color: var(--theme-text, #ffffff);
        font-size: 1.65rem;

        background:
            linear-gradient(
                145deg,
                var(--theme-surface, rgba(31,38,39,0.95)),
                var(--theme-surface, rgba(53,55,58,0.95))
            );


        border-radius: 14px;

        box-shadow:
            0 8px 25px var(--theme-shadow, rgba(0, 0, 0, 0.35)),
            0 0 20px var(--theme-shadow, rgba(13, 202, 240, 0.12));

        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);

        cursor: pointer;

        opacity: 0;
        visibility: hidden;

        transform:
            translateY(15px)
            scale(0.9);

        transition:
            opacity 0.25s ease,
            visibility 0.25s ease,
            transform 0.25s ease,
            box-shadow 0.25s ease,
            background 0.25s ease;

        z-index: 99999;
    }


    /* Visible state */
    #back-to-top.show {
        opacity: 1;
        visibility: visible;

        transform:
            translateY(0)
            scale(1);
    }


    /* Icon */
    #back-to-top i {
        display: flex;
        align-items: center;
        justify-content: center;

        line-height: 1;

        transition: transform 0.25s ease;
    }


    /* Hover */
    #back-to-top:hover {
        color: var(--theme-text, #ffffff);

        background:
            linear-gradient(
                145deg,
                var(--theme-surface, #919596),
                var(--theme-surface, #212222)
            );

        transform:
            translateY(-3px)
            scale(1.04);

        box-shadow:
            0 12px 30px var(--theme-shadow, rgba(0, 0, 0, 0.4)),
            0 0 25px var(--theme-shadow, rgba(13, 202, 240, 0.25));
    }


    #back-to-top:hover i {
        transform: translateY(-2px);
    }


    /* Click */
    #back-to-top:active {
        transform:
            translateY(0)
            scale(0.95);
    }


    /* Keyboard accessibility */
    #back-to-top:focus-visible {
        outline: 3px solid var(--theme-teal-border, rgba(13, 202, 240, 0.35));
        outline-offset: 3px;
    }


    /* =========================================================
       MOBILE
    ========================================================= */

    @media (max-width: 767.98px) {

        #back-to-top {
            right: 15px;
            bottom: 15px;

            width: 44px;
            height: 44px;

            border-radius: 12px;

            font-size: 1rem;
        }

    }
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const backToTopButton = document.getElementById('back-to-top');
    const appMain = document.querySelector('.app-main');

    if (!backToTopButton) {
        return;
    }


    /* =========================================================
       GET CURRENT SCROLL POSITION
    ========================================================= */

    function getScrollPosition() {

        const windowScroll =
            window.pageYOffset ||
            document.documentElement.scrollTop ||
            document.body.scrollTop ||
            0;

        const appMainScroll = appMain
            ? appMain.scrollTop
            : 0;

        return Math.max(
            windowScroll,
            appMainScroll
        );
    }


    /* =========================================================
       SHOW / HIDE BUTTON
    ========================================================= */

    function updateBackToTopButton() {

        const scrollPosition = getScrollPosition();

        if (scrollPosition > 250) {

            backToTopButton.classList.add('show');

        } else {

            backToTopButton.classList.remove('show');

        }

    }


    /* =========================================================
       WINDOW SCROLL
    ========================================================= */

    window.addEventListener(
        'scroll',
        updateBackToTopButton,
        {
            passive: true
        }
    );


    /* =========================================================
       ADMINLTE / APP MAIN SCROLL
    ========================================================= */

    if (appMain) {

        appMain.addEventListener(
            'scroll',
            updateBackToTopButton,
            {
                passive: true
            }
        );

    }


    /* =========================================================
       BACK TO TOP CLICK
    ========================================================= */

    backToTopButton.addEventListener('click', function () {

        /*
         * Scroll the browser window.
         */
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });


        /*
         * AdminLTE can use .app-main as the actual
         * scrolling container on some pages.
         */
        if (appMain) {

            appMain.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

        }

    });


    /* =========================================================
       INITIAL CHECK
    ========================================================= */

    updateBackToTopButton();

});
</script>


    @stack('scripts')

    <script>
document.addEventListener('DOMContentLoaded', function () {

    const badge = document.getElementById('announcement-badge');
    if (!badge) return;

    fetch('/announcements-unread-count', { headers: { Accept: 'application/json' } })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
            if (data && (data.count > 0 || data.count === '9+')) {
                badge.textContent = data.count;
                badge.classList.remove('d-none');
            }
        })
        .catch(error => console.warn('Announcement count could not be loaded.', error));

});
</script>


</body><!--end::Body-->

</html>
