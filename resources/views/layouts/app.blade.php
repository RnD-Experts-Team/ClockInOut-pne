<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PNE Maintenance')</title>

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="PNE ClockIn">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PNE ClockIn">
    <meta name="description" content="Professional clock in/out system for PNE maintenance staff">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <meta name="msapplication-TileColor" content="#ff671b">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="theme-color" content="#ff671b">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- PWA Icons -->
    <link rel="apple-touch-icon" href="/icons/icon-152x152.svg">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152x152.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.svg">
    <link rel="icon" type="image/svg+xml" sizes="32x32" href="/icons/icon-32x32.svg">
    <link rel="icon" type="image/svg+xml" sizes="16x16" href="/icons/icon-16x16.svg">
    <link rel="icon" type="image/svg+xml" sizes="72x72" href="/icons/icon-72x72.svg">
    <link rel="icon" type="image/svg+xml" sizes="96x96" href="/icons/icon-96x96.svg">
    <link rel="icon" type="image/svg+xml" sizes="128x128" href="/icons/icon-128x128.svg">
    <link rel="icon" type="image/svg+xml" sizes="144x144" href="/icons/icon-144x144.svg">
    <link rel="icon" type="image/svg+xml" sizes="192x192" href="/icons/icon-192x192.svg">
    <link rel="icon" type="image/svg+xml" sizes="384x384" href="/icons/icon-384x384.svg">
    <link rel="icon" type="image/svg+xml" sizes="512x512" href="/icons/icon-512x512.svg">
    <link rel="mask-icon" href="/icons/safari-pinned-tab.svg" color="#ff671b">
    <link rel="shortcut icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
{{--    <script src="https://cdn.tailwindcss.com"></script>--}}

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- <link rel="stylesheet" href="{{ URL::asset('build/assets/forms.css') }}"> --}}

</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden bg-gray-100" x-data="{ sidebarOpen: false, mobileOpen: false, sidebarCollapsed: false }">
        <!-- Sidebar -->
        @auth
        <x-sidebar />
        @endauth

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            <!-- Mobile Header -->
            <!-- Mobile Header -->
            @auth
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 lg:hidden">
                <div class="flex items-center">
                    <button @click="mobileOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="ml-4 text-lg font-bold text-orange-600">PNE Maintenance</span>
                </div>
            </header>
            @endauth

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                @if(isset($header))
                    <header class="bg-white shadow rounded-lg mb-6">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="//unpkg.com/alpinejs" defer></script>
    @stack('scripts')
</body>
</html>
