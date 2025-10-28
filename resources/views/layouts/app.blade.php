<!DOCTYPE html>
<html lang="en"> 

@include('layouts.partials.header')

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary" data-bs-theme="dark"> 
    <div class="app-wrapper"> 

    
    @auth

    @include('layouts.partials.navbar')
    @include('layouts.partials.sidebar')



 @endauth

        <main class="app-main"> 

            <div class="app-content"> 
                <div class="container-fluid"> 


                @yield('content')


           </div>


        </div> 
        </main> 
        @auth
        
        <footer class="app-footer bg-dark text-white py-3 mb-1">
           
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <span class="text-muted">
                                <span class="text-primary"> <i class="bi bi-globe2"></i> LastFiles</span>
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mt-2 mt-md-0">
                        <div class="text-md-end">
                            <small class="text-muted">
                                <span class="me-2">Built with Laravel</span>
                                <span class="me-2">|</span>
                                <span> <i class="bi bi-filetype-php"></i> Developed and maintained by <strong>lukan87</strong> </span>
                                <span class="mx-2">|</span>
                                <span>Seed until you bleed</span>
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-12 text-center text-md-start">
                        <small class="text-muted">
                            © <?php echo date('Y'); ?> All rights reserved
                        </small>
                    </div>
                </div>
           
        </footer>
    
        
        <style>
        .app-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9rem;
        }
        .text-primary {
            color: #4da6ff !important;
        }
        </style>
        
        <style>
        .app-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        .app-footer a:hover {
            color: #4da6ff !important;
        }
        .badge {
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        </style>
        @endauth
    </div> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous"></script> 
    <script src="{{ asset('dist/js/adminlte.js') }}"></script> 
 
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<script src="{{ asset('js/lity/litty.js') }}" defer></script>





<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>





@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: @json(session('success')),
            showConfirmButton: true,
        });
    </script>
@endif

@if(session('warning'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: @json(session('warning')),
            showConfirmButton: true,
        });
    </script>
@endif

@if(session('info'))
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Information',
            text: @json(session('info')),
            showConfirmButton: true,
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: @json(session('error')),
            showConfirmButton: true,
        });
    </script>
@endif


<!-- Back to Top Button -->
<button id="back-to-top" class="btn btn-secondary" style="position: fixed; bottom: 100px; right: 20px; display: none;">
<i class="bi bi-arrow-up-circle-fill fs-3"></i>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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



</body><!--end::Body-->

</html>
