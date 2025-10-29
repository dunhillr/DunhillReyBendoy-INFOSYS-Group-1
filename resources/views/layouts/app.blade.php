<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-free">
    <head>
        <meta charset="utf-8" />
        <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Dashboard - Analytics</title>

        @vite(['resources/sass/app.scss', 'resources/js/app.js'])

        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

        <!-- Add jQuery UI CSS Here -->
        <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

        <!-- Optional: Custom CSS for specific dark mode styling, if needed -->
        <style>
            body.dark-mode {
                background-color: #111827; /* dark:bg-gray-900 equivalent */
                color: #e5e7eb; /* Optional for dark mode text */
            }
            .dark-mode .bg-white {
                background-color: #1f2937 !important; /* dark:bg-gray-800 equivalent */
            }
            .ui-autocomplete {
                z-index: 9999 !important;
                background: #fff;
                border: 1px solid #ddd;
                max-height: 200px;
                overflow-y: auto;
            }

            .ui-menu-item-wrapper {
                padding: 8px 12px;
                cursor: pointer;
            }

            .ui-menu-item-wrapper:hover,
            .ui-menu-item-wrapper.ui-state-active {
                background: #0d6efd; /* Matches Bootstrap primary */
                color: white;
            }

            .nav-link:not(.active):hover {
                background-color: rgba(13, 110, 253, 0.1);
                color: #0d6efd;
                transition: 0.2s;
            }

            .nav-link:active {
                transform: scale(0.97);
                transition: transform 0.1s;
            }

        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-vh-100 bg-light">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Consolidate Scripts and ensure correct order -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="//code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @stack('scripts')
    </body>
</html>
