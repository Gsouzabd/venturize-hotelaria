<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" class="light-style layout-offcanvas">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge,chrome=1">
    <meta name="description" content="">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">

    <title>{{ (view()->hasSection('title') ? view()->getSection('title') . ' - ' : '') . config('app.name') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/ionicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/roboto.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/appwork/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/appwork/appwork.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/appwork/theme-corporate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/appwork/colors.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/appwork/uikit.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/appwork/pages/authentication.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/appwork/pages/projects.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/appwork/pages/users.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/spinkit/spinkit.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/sweetalert2/sweetalert2.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('assets/admin/app.css') }}">
    @stack('styles')
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>
        window.APP_HOME_URL = "{{ url('/') }}";
        window.APP_CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
</head>

<body{!! view()->hasSection('body-class') ? ' class="'. view()->getSection('body-class') . '"' : '' !!}>
@yield('body')

@if(!view()->hasSection('body'))
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-1">
        <div class="layout-inner">

            <!-- Layout navbar -->
            @include('layouts.admin.includes.navbar')
            <!-- / Layout navbar -->

            <!-- Layout container -->
            <div class="layout-container">

                @if(!config('app.horizontal_sidenav'))
                    <!-- Layout sidenav -->
                    @include('layouts.admin.includes.sidenav')
                    <!-- / Layout sidenav -->
                @endif

                <!-- Layout content -->
                <div class="layout-content">

                    @if(config('app.horizontal_sidenav'))
                        <!-- Layout sidenav -->
                        @include('layouts.admin.includes.sidenav')
                        <!-- / Layout sidenav -->
                    @endif

                    <!-- Content -->
                    <div class="container-fluid flex-grow-1 container-p-y d-flex flex-column">

                        @yield('content-header')

                        @stack('notices')
                        @include('layouts.admin.includes.notices')

                        @yield('content')

                    </div>
                    <!-- / Content -->

                    <!-- Layout footer -->
                    @include('layouts.admin.includes.footer')
                    <!-- / Layout footer -->

                </div>
                <!-- Layout content -->

            </div>
            <!-- / Layout container -->

        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-sidenav-toggle"></div>
    </div>
    <!-- / Layout wrapper -->
@endif

@stack('footer')

<script src="{{ asset('assets/admin/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/admin/vendor/moment/moment.js') }}"></script>
<script src="{{ asset('assets/admin/vendor/popper/popper.js') }}"></script>

<script src="{{ asset('assets/admin/appwork/layout-helpers.js') }}"></script>
<script src="{{ asset('assets/admin/appwork/bootstrap.js') }}"></script>
<script src="{{ asset('assets/admin/appwork/sidenav.js') }}"></script>

<script src="{{ asset('assets/admin/vendor/block-ui/block-ui.js') }}"></script>
<script src="{{ asset('assets/admin/vendor/jquery-mask/jquery.mask.min.js') }}"></script>
<script src="{{ asset('assets/admin/vendor/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/admin/vendor/sweetalert2/sweetalert2.js') }}"></script>

<script src="{{ asset('assets/admin/helpers.js') }}"></script>
<script src="{{ asset('assets/admin/app.js') }}"></script>
@stack('scripts')
</body>
</html>
