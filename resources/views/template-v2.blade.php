<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <!-- SB Admin 2 CSS -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">



</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    @include('layouts.partials.sidebar')
    <!-- End Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            @include('layouts.partials.topbar')
            <!-- End Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid ">

                @yield('content')

            </div>
            <!-- End Page Content -->

        </div>
        <!-- End Main Content -->

    </div>
    <!-- End Content Wrapper -->

</div>
<!-- End Page Wrapper -->

{{--<!-- Scripts -->--}}
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
{{--<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>--}}
{{--<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>--}}

{{--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>--}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@yield('scripts')
</body>

</html>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const body = document.body;
        const wrapper = document.getElementById("content-wrapper");
        const content = document.getElementById("content");
        const fluid = document.querySelector(".container-fluid");

        const themeToggle = document.getElementById("themeToggle");
        const themeIcon = document.getElementById("themeIcon");

        function applyDarkMode() {
            body.classList.add("dark-mode");
            wrapper.classList.add("dark-mode");
            content.classList.add("dark-mode");
            fluid.classList.add("dark-mode");

            themeIcon.classList.remove("fa-moon");
            themeIcon.classList.add("fa-sun");
            body.classList.add("dark-mode");

        }

        function applyLightMode() {
            body.classList.remove("dark-mode");
            wrapper.classList.remove("dark-mode");
            content.classList.remove("dark-mode");
            fluid.classList.remove("dark-mode");

            themeIcon.classList.remove("fa-sun");
            themeIcon.classList.add("fa-moon");
        }

        // Cargar tema guardado
        if (localStorage.getItem("theme") === "dark") {
            applyDarkMode();
        }

        themeToggle.addEventListener("click", function () {

            if (!body.classList.contains("dark-mode")) {
                applyDarkMode();
                localStorage.setItem("theme", "dark");
            } else {
                applyLightMode();
                localStorage.setItem("theme", "light");
            }
        });

    });

</script>
