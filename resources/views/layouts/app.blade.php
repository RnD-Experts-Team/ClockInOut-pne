<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PNE Maintenance')</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/assets/forms.css') }}">
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
                    <svg class="w-10 h-10 text-orange-600 relative transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
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
                                    Payments
                                    <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-orange-50 ring-1 ring-orange-900/5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="payments-menu" id="payments-dropdown">
                                <div class="py-1" role="none">
                                    <a href="{{ route('payments.index') }}" class="block px-4 py-2 text-sm text-orange-700 hover:bg-orange-100 hover:text-orange-900" role="menuitem">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Payment Tracker
                                    </a>
                                    <a href="{{ route('companies.index') }}" class="block px-4 py-2 text-sm text-orange-700 hover:bg-orange-100 hover:text-orange-900" role="menuitem">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <button type="button" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg" id="leases-menu" aria-expanded="false" aria-haspopup="true">
                                    <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                                    </svg>
                                    Leases
                                    <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-orange-50 ring-1 ring-orange-900/5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="leases-menu" id="leases-dropdown">
                                <div class="py-1" role="none">
                                    <a href="{{ route('leases.index') }}" class="block px-4 py-2 text-sm text-orange-700 hover:bg-orange-100 hover:text-orange-900" role="menuitem">Store Leases</a>
                                    <a href="{{ route('admin.apartment-leases.index') }}" class="block px-4 py-2 text-sm text-orange-700 hover:bg-orange-100 hover:text-orange-900" role="menuitem">Apartment Leases</a>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="relative inline-block text-left">
                            <div>
                                <button type="button" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg" id="settings-menu" aria-expanded="false" aria-haspopup="true">
                                    <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Settings
                                    <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-orange-50 ring-1 ring-orange-900/5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="settings-menu" id="settings-dropdown">
                                <div class="py-1" role="none">
                                    <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-orange-700 hover:bg-orange-100 hover:text-orange-900" role="menuitem">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        User Management
                                    </a>
                                    <a href="{{ route('stores.index') }}" class="block px-4 py-2 text-sm text-orange-700 hover:bg-orange-100 hover:text-orange-900" role="menuitem">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                                        </svg>
                                        Store Management
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('attendance.index') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            الحضور
                        </a>
                        <a href="{{ route('clocking.index') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            تسجيل الحضور والانصراف
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>

                <!-- Mobile Menu Button -->
                <button type="button"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-orange-400 hover:text-orange-500 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-orange-500 transition-colors duration-300"
                        onclick="toggleMobileMenu()">
                    <span class="sr-only">Open main menu</span>
                    <svg class="block h-6 w-6" id="menuIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg class="hidden h-6 w-6" id="closeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endauth
        </div>

        <!-- Mobile Menu -->
        @auth
            <div class="md:hidden hidden" id="mobileMenu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.clocking') }}"
                           class="block px-3 py-2 rounded-md text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-300 mb-2">
                            Clocking Records
                        </a>
                        <a href="{{ route('maintenance-requests.index') }}"
                           class="block px-3 py-2 rounded-md text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-300 mb-2">
                            Maintenance
                        </a>

                        <!-- Mobile Payments Dropdown -->
                        <div class="relative mb-2">
                            <button type="button" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-300" onclick="togglePaymentsMenu()">
                                Payments
                                <svg class="inline-block ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="hidden mt-1 space-y-1" id="mobilePaymentsMenu">
                                <a href="{{ route('payments.index') }}" class="block px-4 py-2 rounded-md text-sm font-medium text-white bg-orange-600/80 hover:bg-orange-700 transition-colors duration-300">
                                    Payment Tracker
                                </a>
                                <a href="{{ route('companies.index') }}" class="block px-4 py-2 rounded-md text-sm font-medium text-white bg-orange-600/80 hover:bg-orange-700 transition-colors duration-300">
                                    Companies
                                </a>
                                <a href="{{ route('payments.dashboard') }}" class="block px-4 py-2 rounded-md text-sm font-medium text-white bg-orange-600/80 hover:bg-orange-700 transition-colors duration-300">
                                    Dashboard
                                </a>
                            </div>
                        </div>

                        <!-- Mobile Leases Dropdown -->
                        <div class="relative mb-2">
                            <button type="button" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-300" onclick="toggleLeasesMenu()">
                                Leases
                                <svg class="inline-block ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="hidden mt-1 space-y-1" id="mobileLeasesMenu">
                                <a href="{{ route('leases.index') }}" class="block px-4 py-2 rounded-md text-sm font-medium text-white bg-orange-600/80 hover:bg-orange-700 transition-colors duration-300">
                                    Store Leases
                                </a>
                                <a href="{{ route('admin.apartment-leases.index') }}" class="block px-4 py-2 rounded-md text-sm font-medium text-white bg-orange-600/80 hover:bg-orange-700 transition-colors duration-300">
                                    Apartment Leases
                                </a>
                            </div>
                        </div>

                        <!-- Mobile Settings Dropdown -->
                        <div class="relative mb-2">
                            <button type="button" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-300" onclick="toggleSettingsMenu()">
                                Settings
                                <svg class="inline-block ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="hidden mt-1 space-y-1" id="mobileSettingsMenu">
                                <a href="{{ route('users.index') }}" class="block px-4 py-2 rounded-md text-sm font-medium text-white bg-orange-600/80 hover:bg-orange-700 transition-colors duration-300">
                                    User Management
                                </a>
                                <a href="{{ route('stores.index') }}" class="block px-4 py-2 rounded-md text-sm font-medium text-white bg-orange-600/80 hover:bg-orange-700 transition-colors duration-300">
                                    Store Management
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('attendance.index') }}"
                           class="block px-3 py-2 rounded-md text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-300 mb-2">
                            الحضور
                        </a>
                        <a href="{{ route('clocking.index') }}"
                           class="block px-3 py-2 rounded-md text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-300 mb-2">
                            تسجيل الحضور والانصراف
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-3 py-2 mt-1 rounded-md text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-300">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </nav>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuIcon = document.getElementById('menuIcon');
            const closeIcon = document.getElementById('closeIcon');

            if (mobileMenu.classList.contains('hidden')) {
                // Show menu
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('animate-fade-in');
                menuIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                // Hide menu
                mobileMenu.classList.add('hidden');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        }

        function togglePaymentsMenu() {
            const paymentsMenu = document.getElementById('mobilePaymentsMenu');
            paymentsMenu.classList.toggle('hidden');
        }

        function toggleLeasesMenu() {
            const leasesMenu = document.getElementById('mobileLeasesMenu');
            leasesMenu.classList.toggle('hidden');
        }

        function toggleSettingsMenu() {
            const settingsMenu = document.getElementById('mobileSettingsMenu');
            settingsMenu.classList.toggle('hidden');
        }

        // Desktop dropdown functionality
        const dropdowns = [
            { button: 'payments-menu', dropdown: 'payments-dropdown' },
            { button: 'leases-menu', dropdown: 'leases-dropdown' },
            { button: 'settings-menu', dropdown: 'settings-dropdown' }
        ];

        dropdowns.forEach(({ button: buttonId, dropdown: dropdownId }) => {
            const button = document.getElementById(buttonId);
            const dropdown = document.getElementById(dropdownId);

            if (button && dropdown) {
                button.addEventListener('click', function() {
                    const expanded = this.getAttribute('aria-expanded') === 'true';

                    // Close all dropdowns
                    dropdowns.forEach(({ button: otherButtonId, dropdown: otherDropdownId }) => {
                        const otherButton = document.getElementById(otherButtonId);
                        const otherDropdown = document.getElementById(otherDropdownId);
                        if (otherButton && otherDropdown && otherButtonId !== buttonId) {
                            otherButton.setAttribute('aria-expanded', 'false');
                            otherDropdown.classList.add('hidden');
                        }
                    });

                    // Toggle current dropdown
                    this.setAttribute('aria-expanded', !expanded);
                    dropdown.classList.toggle('hidden');
                });
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            dropdowns.forEach(({ button: buttonId, dropdown: dropdownId }) => {
                const button = document.getElementById(buttonId);
                const dropdown = document.getElementById(dropdownId);

                if (button && dropdown && !button.contains(event.target) && !dropdown.contains(event.target)) {
                    button.setAttribute('aria-expanded', 'false');
                    dropdown.classList.add('hidden');
                }
            });
        });

        // Close mobile menu on window resize if screen becomes larger
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) { // md breakpoint
                const mobileMenu = document.getElementById('mobileMenu');
                const menuIcon = document.getElementById('menuIcon');
                const closeIcon = document.getElementById('closeIcon');

                if (mobileMenu) mobileMenu.classList.add('hidden');
                if (menuIcon) menuIcon.classList.remove('hidden');
                if (closeIcon) closeIcon.classList.add('hidden');
            }
        });
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* RTL Support */
        [dir="rtl"] .space-x-reverse > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }

        /* Hover Effects */
        .group:hover .group-hover\:scale-110 {
            transform: scale(1.1);
        }

        /* Focus Styles */
        .focus\:ring-2:focus {
            box-shadow: 0 0 0 2px rgba(255, 103, 27, 0.5);
        }

        input, textarea, select {
            border: 2px solid #ff671b;
        }
    </style>
</header>

<main class="container mx-auto px-4 py-8">
    <div class="bg-orange-50 shadow-md rounded-lg p-6">
        @yield('content')
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

</body>
</html>
