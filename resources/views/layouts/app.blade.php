<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PNE Clocking')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white shadow-md">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">PNE Clocking</h1>

            @auth
                <div class="flex items-center space-x-4">
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.clocking') }}" class="text-white hover:text-blue-200">Clocking Records</a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-white hover:text-blue-200">Logout</button>
                    </form>
                </div>
            @endauth
        </nav>
    </header>

    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
