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
{{--    @vite(['resources/css/app.css', 'resources/js/app.js']);--}}
    <script src="https://cdn.tailwindcss.com"></script>

{{--    <link rel="stylesheet" href="{{asset('/build/assets/app-0igVw8Lk.css')}}">--}}
{{--    <script src="{{asset('/build/assets/app-BGDHvcUF.js')}}"></script>--}}

{{--    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">--}}
{{--    <link rel="stylesheet" href="{{ URL::asset('build/assets/forms.css') }}">--}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'],
                        serif: ['Merriweather', 'ui-serif', 'Georgia', 'Cambria', 'Times New Roman', 'Times', 'serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', 'monospace'],
                    },
                    colors: {
                        'primary': '#ff671b',
                        'primary-dark': '#c74f14', // Adjusted to a darker orange shade for hover
                    },
                    borderColor: {
                        'primary': '#ff671b',
                    },
                    spacing: {
                        '128': '32rem',
                        '144': '36rem',
                    },
                    borderRadius: {
                        '4xl': '2rem',
                    },
                },
            }
        }
    </script>
</head>
<body class="bg-orange-50 font-sans">
<header class="bg-orange-50 shadow-lg border-b border-orange-100">
    <nav class="container mx-auto px-4 lg:px-8 py-4">
        <div class="flex justify-between items-center">
            <!-- Logo Section -->
            @php
                $href = '/';
                if(auth()->check()){
                    $href = auth()->user()->role === 'admin' ? '/admin/clockings' : '/clocking';
                }
            @endphp

            <a href="{{ $href }}" class="flex items-center space-x-3 rtl:space-x-reverse group">
                <div class="relative">
                    <div class="absolute inset-0 bg-orange-100 rounded-full blur-lg transform group-hover:scale-110 transition-transform duration-300"></div>
                    <svg class="w-10 h-10 text-orange-600 relative transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold hidden sm:block relative group/title">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r bg-orange-600 relative inline-block transform transition-transform duration-300 group-hover/title:scale-105 drop-shadow-[0_2px_2px_rgba(0,0,0,0.1)]">PNE</span>
                    <span class="text-orange-800 inline-block transform transition-transform duration-300 group-hover/title:scale-105 ml-2 drop-shadow-[0_1px_1px_rgba(0,0,0,0.05)]">Maintenance</span>
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-100 to-orange-200 blur-xl opacity-0 group-hover/title:opacity-100 transition-opacity duration-500 -z-10"></div>
                </h1>
            </a>

            @auth
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-4 rtl:space-x-reverse">
                    @if(Auth::user()->role === 'admin')
                        <!-- Schedule Management Dropdown -->
                        <div class="relative inline-block text-left">
                            <div>
                                <button type="button" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg" id="schedule-menu" aria-expanded="false" aria-haspopup="true">
                                    <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 0l-8 4v12l8-4m0 0l8 4M12 11v8"></path>
                                    </svg>
                                    <span class="hidden xl:inline">Schedule</span>
                                    <span class="xl:hidden">Sched</span>
                                    <svg class="-mr-1 ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden z-50" role="menu" aria-orientation="vertical" aria-labelledby="schedule-menu" id="schedule-dropdown">
                                <div class="py-1" role="none">
                                    <a href="{{ route('admin.schedules.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        All Schedules
                                    </a>
                                    <a href="{{ route('admin.schedules.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Create Schedule
                                    </a>
                                    <a href="{{ route('admin.task-assignments.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m.121 9.243l4.95 4.95a2.121 2.121 0 0 0 3-3L13.243 8.364a2.121 2.121 0 0 0-3 3L15.121 16.121Z"></path>
                                        </svg>
                                        Task Assignments
                                    </a>
                                    <a href="{{ route('admin.scorecards.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4z"></path>
                                        </svg>
                                        Scorecards
                                    </a>
                                </div>
                            </div>
                        </div>


                        <a href="{{ route('admin.clocking') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Clocking Records
                        </a>

                        <a href="{{ route('maintenance-requests.index') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Maintenance
                        </a>

                        <!-- Payments Dropdown -->
                        <div class="relative inline-block text-left">
                            <div>
                                <button type="button" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg" id="payments-menu" aria-expanded="false" aria-haspopup="true">
                                    <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="hidden xl:inline">Payments</span>
                                    <span class="xl:hidden">Pay</span>
                                    <svg class="-mr-1 ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden z-50" role="menu" aria-orientation="vertical" aria-labelledby="payments-menu" id="payments-dropdown">
                                <div class="py-1" role="none">
                                    <a href="{{ route('payments.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Payment Tracker
                                    </a>
                                    <a href="{{ route('companies.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                                        </svg>
                                        Companies
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Leases Dropdown -->
                        <div class="relative inline-block text-left">
                            <div>
                                <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg hover:scale-105" id="leases-menu" aria-expanded="false" aria-haspopup="true">
                                    <svg class="w-4 h-4 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                                    </svg>
                                    <span class="hidden xl:inline">Leases</span>
                                    <span class="xl:hidden">Lease</span>
                                    <svg class="-mr-1 ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden z-50" role="menu" aria-orientation="vertical" aria-labelledby="leases-menu" id="leases-dropdown">
                                <div class="py-1" role="none">
                                    <a href="{{ route('leases.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">Store Leases</a>
                                    <a href="{{ route('admin.apartment-leases.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">Apartment Leases</a>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="relative inline-block text-left">
                            <div>
                                <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg hover:scale-105" id="settings-menu" aria-expanded="false" aria-haspopup="true">
                                    <svg class="w-4 h-4 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="hidden xl:inline">Settings</span>
                                    <span class="xl:hidden">Set</span>
                                    <svg class="-mr-1 ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden z-50" role="menu" aria-orientation="vertical" aria-labelledby="settings-menu" id="settings-dropdown">
                                <div class="py-1" role="none">
                                    <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        User Management
                                    </a>
                                    <a href="{{ route('stores.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-900 transition-colors duration-200" role="menuitem">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                                        </svg>
                                        Store Management
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                            <!-- Regular User Navigation with Enhanced Buttons -->

                        <a href="{{ route('user.schedule.index') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="hidden xl:inline">{{ __('messages.my_schedule') }}</span>
                            <span class="xl:hidden">{{ __('messages.schedule') }}</span>
                        </a>

                        <!-- My Tasks Button -->
                        <a href="{{ route('user.tasks.index') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m.121 9.243l4.95 4.95a2.121 2.121 0 0 0 3-3L13.243 8.364a2.121 2.121 0 0 0-3 3L15.121 16.121Z"></path>
                            </svg>
                            <span class="hidden xl:inline">{{ __('messages.my_tasks') }}</span>
                            <span class="xl:hidden">{{ __('messages.tasks') }}</span>
                        </a>

                            <!-- Existing buttons remain the same -->

                        <a href="{{ route('attendance.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            {{ __('messages.attendance') }}
                        </a>
                        <a href="{{ route('clocking.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ __('messages.clock_in_registration') }}
                        </a>
                    @endif

                    <!-- Language Selector -->
                    <button onclick="openLanguageModal()" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-orange-600 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        🌐
                    </button>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-300 hover:shadow-lg hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="hidden xl:inline">{{ __('messages.logout') }}</span>
                            <span class="xl:hidden">Exit</span>
                        </button>
                    </form>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden flex items-center space-x-2">
                    <!-- Language Toggle for Mobile -->
                    <button onclick="openLanguageModal()" class="inline-flex items-center p-2 rounded-lg text-orange-600 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                    </button>

                    <!-- Mobile Menu Toggle -->
                    <button onclick="toggleMobileMenu()" class="inline-flex items-center p-2 rounded-lg text-orange-600 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-300" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <!-- Hamburger Icon -->
                        <svg id="menuIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <!-- Close Icon -->
                        <svg id="closeIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endauth
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="lg:hidden hidden bg-white border-t border-orange-200 shadow-lg">
        <div class="px-4 pt-2 pb-3 space-y-1">
            @auth
                @if(auth()->user()->role === 'admin')
                    <!-- Admin mobile menu items -->
                    <a href="{{ route('admin.clocking') }}" class="flex items-center px-3 py-3 text-base font-medium text-gray-700 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Clocking Records
                    </a>
                    <a href="{{ route('maintenance-requests.index') }}" class="flex items-center px-3 py-3 text-base font-medium text-gray-700 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Maintenance
                    </a>

                    <!-- Mobile Payments Section -->
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Payments</div>
                        <a href="{{ route('payments.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Payment Tracker
                        </a>
                        <a href="{{ route('companies.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                            </svg>
                            Companies
                        </a>
                    </div>

                    <!-- Mobile Leases Section -->
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Leases</div>
                        <a href="{{ route('leases.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                            </svg>
                            Store Leases
                        </a>
                        <a href="{{ route('admin.apartment-leases.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                            </svg>
                            Apartment Leases
                        </a>
                    </div>

                    <!-- Mobile Settings Section -->
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Settings</div>
                        <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            User Management
                        </a>
                        <a href="{{ route('stores.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                            </svg>
                            Store Management
                        </a>
                    </div>
                @else
                    <!-- Regular user mobile menu items -->
                    <a href="{{ route('user.schedule.index') }}" class="flex items-center px-3 py-3 text-base font-medium text-gray-700 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('messages.my_schedule') }}
                    </a>

                    <!-- My Tasks -->
                    <a href="{{ route('user.tasks.index') }}" class="flex items-center px-3 py-3 text-base font-medium text-gray-700 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-all duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m.121 9.243l4.95 4.95a2.121 2.121 0 0 0 3-3L13.243 8.364a2.121 2.121 0 0 0-3 3L15.121 16.121Z"></path>
                        </svg>
                        {{ __('messages.my_tasks') }}
                    </a>

                    <a href="{{ route('attendance.index') }}" class="flex items-center px-3 py-3 text-base font-medium text-gray-700 hover:text-orange-900 hover:bg-orange-50">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        {{ __('messages.attendance') }}
                    </a>
                    <a href="{{ route('clocking.index') }}" class="flex items-center px-3 py-3 text-base font-medium text-gray-700 hover:text-orange-900 hover:bg-orange-50">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('messages.clock_in_registration') }}
                    </a>
                @endif

                <!-- Mobile Logout -->
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-3 py-3 text-base font-medium text-red-600 hover:text-red-900 hover:bg-red-50">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            {{ __('messages.logout') }}
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</header>

<!-- Fix the main content structure (around lines 445-465) -->
<main class="container mx-auto px-4 py-8">
    <div class="bg-orange-50 shadow-md rounded-lg p-6">
        @yield('content')

        <!-- Language Selector Modal -->
        @include('components.language-selector')
    </div>
</main>

<footer class="bg-orange-100 border-t mt-12">
    <div class="container mx-auto px-4 py-6 text-center text-orange-600">
        &copy; {{ date('Y') }} PNE Maintenance.
        <p class="text-center text-orange-500">
            Crafted with <span class="text-orange-500">&hearts;</span> by the R&D Team.
        </p>
    </div>
</footer>

<!-- PWA Service Worker Registration -->
<script>
    // Register service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);

                    // Check for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New content available, show update notification
                                showUpdateNotification();
                            }
                        });
                    });
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }

    // PWA Install Prompt
    let deferredPrompt;
    let installButton = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('PWA install prompt triggered');
        e.preventDefault();
        deferredPrompt = e;
        showInstallButton();
    });

    function showInstallButton() {
        // Create install button if it doesn't exist
        if (!installButton) {
            installButton = document.createElement('button');
            installButton.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Install App
            `;
            installButton.className = 'fixed bottom-4 right-4 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center text-sm font-medium transition-all duration-200 z-50';
            installButton.onclick = installPWA;
            document.body.appendChild(installButton);
        }
        installButton.style.display = 'flex';
    }

    function installPWA() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                } else {
                    console.log('User dismissed the install prompt');
                }
                deferredPrompt = null;
                if (installButton) {
                    installButton.style.display = 'none';
                }
            });
        }
    }

    // Hide install button when app is installed
    window.addEventListener('appinstalled', (evt) => {
        console.log('PWA was installed');
        if (installButton) {
            installButton.style.display = 'none';
        }
    });

    // Show update notification
    function showUpdateNotification() {
        const updateBanner = document.createElement('div');
        updateBanner.innerHTML = `
            <div class="bg-blue-500 text-white px-4 py-3 fixed top-0 left-0 right-0 z-50 flex items-center justify-between">
                <span>A new version is available!</span>
                <button onclick="updateApp()" class="bg-white text-blue-500 px-3 py-1 rounded text-sm font-medium">Update</button>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 ml-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(updateBanner);
    }

    function updateApp() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistration().then(registration => {
                if (registration && registration.waiting) {
                    registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                    window.location.reload();
                }
            });
        }
    }

    // Handle offline/online status
    function updateOnlineStatus() {
        const statusIndicator = document.getElementById('online-status');
        if (!statusIndicator) {
            const indicator = document.createElement('div');
            indicator.id = 'online-status';
            indicator.className = 'fixed top-4 right-4 px-3 py-1 rounded-full text-sm font-medium z-40';
            document.body.appendChild(indicator);
        }

        const indicator = document.getElementById('online-status');
        if (navigator.onLine) {
            indicator.textContent = 'Online';
            indicator.className = 'fixed top-4 right-4 px-3 py-1 rounded-full text-sm font-medium z-40 bg-green-500 text-white';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 3000);
        } else {
            indicator.textContent = 'Offline';
            indicator.className = 'fixed top-4 right-4 px-3 py-1 rounded-full text-sm font-medium z-40 bg-red-500 text-white';
            indicator.style.display = 'block';
        }
    }

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);

    // Initialize online status
    document.addEventListener('DOMContentLoaded', updateOnlineStatus);

    // Dropdown functionality
    function toggleDropdown(dropdownId, buttonId) {
        const dropdown = document.getElementById(dropdownId);
        const button = document.getElementById(buttonId);
        const isHidden = dropdown.classList.contains('hidden');

        // Close all other dropdowns
        document.querySelectorAll('[id$="-dropdown"]').forEach(dd => {
            if (dd.id !== dropdownId) {
                dd.classList.add('hidden');
            }
        });

        // Toggle current dropdown
        if (isHidden) {
            dropdown.classList.remove('hidden');
            button.setAttribute('aria-expanded', 'true');
        } else {
            dropdown.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
        }
    }

    // Mobile menu toggle
    function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        const menuIcon = document.getElementById('menuIcon');
        const closeIcon = document.getElementById('closeIcon');

        if (mobileMenu.classList.contains('hidden')) {
            mobileMenu.classList.remove('hidden');
            menuIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
        } else {
            mobileMenu.classList.add('hidden');
            menuIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('[id$="-dropdown"]');
        const buttons = document.querySelectorAll('[id$="-menu"]');

        let clickedInsideDropdown = false;

        // Check if click was inside any dropdown or button
        dropdowns.forEach(dropdown => {
            if (dropdown.contains(event.target)) {
                clickedInsideDropdown = true;
            }
        });

        buttons.forEach(button => {
            if (button.contains(event.target)) {
                clickedInsideDropdown = true;
            }
        });

        // Close all dropdowns if click was outside
        if (!clickedInsideDropdown) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
            buttons.forEach(button => {
                button.setAttribute('aria-expanded', 'false');
            });
        }
    });

    // Add click event listeners to dropdown buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Payments dropdown
        const paymentsButton = document.getElementById('payments-menu');
        if (paymentsButton) {
            paymentsButton.addEventListener('click', function(e) {
                e.preventDefault();
                toggleDropdown('payments-dropdown', 'payments-menu');
            });
        }

        // Leases dropdown
        const leasesButton = document.getElementById('leases-menu');
        if (leasesButton) {
            leasesButton.addEventListener('click', function(e) {
                e.preventDefault();
                toggleDropdown('leases-dropdown', 'leases-menu');
            });
        }

        // Settings dropdown
        const settingsButton = document.getElementById('settings-menu');
        if (settingsButton) {
            settingsButton.addEventListener('click', function(e) {
                e.preventDefault();
                toggleDropdown('settings-dropdown', 'settings-menu');
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Schedule dropdown
        const scheduleButton = document.getElementById('schedule-menu');
        if (scheduleButton) {
            scheduleButton.addEventListener('click', function(e) {
                e.preventDefault();
                toggleDropdown('schedule-dropdown', 'schedule-menu');
            });
        }
    });
</script>

</body>
</html>
