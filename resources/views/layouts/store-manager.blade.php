<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Store Manager') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    {{-- Minimal Top Navigation Bar --}}
    <nav class="bg-white shadow-md border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold text-orange-600">{{ config('app.name', 'Maintenance System') }}</span>
                    </div>
                </div>

                {{-- Center Navigation --}}
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('native.requests.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Request
                    </a>
                    <a href="{{ route('native.requests.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
                        My Tickets
                    </a>
                </div>

                {{-- User Menu --}}
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600 hidden sm:block">
                        {{ auth()->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div class="md:hidden border-t border-gray-200">
            <div class="px-4 py-3 space-y-2">
                <a href="{{ route('native.requests.create') }}" 
                   class="block px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg text-center">
                    + New Request
                </a>
                <a href="{{ route('native.requests.index') }}" 
                   class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-orange-50 rounded-lg">
                    My Tickets
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Content Area --}}
    <main class="py-6">
        @yield('content')
    </main>
</body>
</html>
