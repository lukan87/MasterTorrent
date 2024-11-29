<!DOCTYPE html>
<html lang="en"> <!--begin::Head-->

@include('layouts.partials.header')

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary" data-bs-theme="dark"> <!--begin::App Wrapper-->
    <div class="app-wrapper"> <!--begin::Header-->

    <!-- Check if user is logged in -->
    @auth

    @include('layouts.partials.navbar')
    @include('layouts.partials.sidebar')



 @endauth

        <main class="app-main"> <!--begin::App Content Header-->

            <div class="app-content"> <!--begin::Container-->
                <div class="container-fluid"> <!-- Info boxes -->


                @yield('content')


           </div>


        </div> <!--end::App Content-->
        </main> <!--end::App Main--> <!--begin::Footer-->
        @auth
        <footer class="app-footer"> <!--begin::To the end-->
            <div class="float-end d-none d-sm-inline">Seed until you bleed</div> <!--end::To the end--> <!--begin::Copyright--> <strong>
                Copyright &copy; 2024&nbsp;
                <a href="/" class="text-decoration-none">MyTorrents</a>.
            </strong>
            All rights reserved.
            <!--end::Copyright-->
        </footer> <!--end::Footer-->
        @endauth
    </div> <!--end::App Wrapper--> <!--begin::Script--> <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script> <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script> <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous"></script> <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('dist/js/adminlte.js') }}"></script> <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
 <!--end::OverlayScrollbars Configure--> <!-- OPTIONAL SCRIPTS --> <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<!-- mine -->

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
            text: "{{ session('success') }}",
            showConfirmButton: true,
        });
    </script>
@endif

@if(session('warning'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: "{{ session('warning') }}",
            showConfirmButton: true,
        });
    </script>
@endif

@if(session('info'))
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Information',
            text: "{{ session('info') }}",
            showConfirmButton: true,
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}",
            showConfirmButton: true,
        });
    </script>
@endif

<!-- Back to Top Button -->
<button id="back-to-top" class="btn btn-info" style="position: fixed; bottom: 20px; right: 20px; display: none;">
    ↑ Back to Top
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

</body><!--end::Body-->

</html>
