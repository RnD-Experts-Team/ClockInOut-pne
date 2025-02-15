<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PNE Clocking')</title>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <link rel="stylesheet" href="{{asset('build/assets/app-DosSlAWK.css')}}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // tailwind.config = {
        //     theme: {
        //         extend: {
        //             fontFamily: {
        //                 sans: ['Inter', 'sans-serif'],
        //             },
        //             colors: {
        //                 'primary': '#3B82F6',
        //                 'primary-dark': '#2563EB',
        //             },
        //         }
        //     }
        // }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <header class="bg-white shadow-sm">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            @php
                $href = '/';
                if(auth()->check()){
                    $href = auth()->user()->role === 'admin' ? '/admin/clockings' : '/clocking';
                }
            @endphp
            
            <a href="{{ $href }}" class="flex items-center space-x-2">
                <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h1 class="text-2xl font-bold text-gray-800">PNE Clocking</h1>
            </a>
            
            @auth
                <div class="flex items-center space-x-6">
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.clocking') }}" class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">Clocking Records</a>
                        <a href="{{ route('users.index') }}" class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">Users</a>
                    @else
                        <!-- New button for authenticated users with "user" role -->
                        <a href="{{ route('attendance.index') }}" class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                            الحضور
                        </a>
                    @endif
    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                            Logout
                        </button>
                    </form>
                </div>
            @endauth
        </nav>
    </header>
    
    

    <main class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            @yield('content')
        </div>
    </main>

    <footer class="bg-gray-100 border-t mt-12">
        <div class="container mx-auto px-4 py-6 text-center text-gray-600">
            &copy; {{ date('Y') }} PNE Clocking.
            <p class="text-center text-gray-500">
                Crafted with <span class="text-red-500">&hearts;</span> by the R&amp;D Team.
            </p>
        </div>
    </footer>

    {{-- <script src="{{asset('build/assets/app-CbEvcXly.js')}}"></script> --}}
</body>
</html>