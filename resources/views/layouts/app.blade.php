<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-free">
    <head>
        <meta charset="utf-8" />
        <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="products-data-url" content="{{ route('products.data') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite([
            'resources/sass/app.scss', 
            'resources/js/app.js'])

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    </head>
    <body class="bg-light">
        {{-- 1. Header (Full Width, Fixed Height) --}}
        @include('layouts.header') 
        
        {{-- 2. Main Content Wrapper --}}
        {{-- D-flex wrapper contains both fixed sidebar and scrollable content --}}
        <div class="d-flex" style="min-height: calc(100vh - 56px);">
        
            {{-- 3. Sidebar (Now fixed, but constrained by viewport size) --}}
            @include('layouts.navigation')
        
            {{-- 4. Page Content --}}
            <main class="content-wrapper flex-grow-1 p-4">
                {{ $slot }}
            </main>
        
        </div>
    
        {{-- 5. Footer (Full Width, Fixed Height) --}}
        @include('layouts.footer')

        <!-- Consolidate Scripts and ensure correct order -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="//code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @stack('scripts')
    </body>
</html>
