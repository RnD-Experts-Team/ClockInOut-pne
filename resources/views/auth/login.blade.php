@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <h2 class="text-3xl font-bold text-center mb-8">Login</h2>
    <form action="{{ route('login') }}" method="POST" class="max-w-md mx-auto space-y-6">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your email" required>
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white font-bold py-3 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out">
            Login
        </button>
    </form>
@endsection
