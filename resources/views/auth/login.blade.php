@extends('layouts.app')

@section('title', __('messages.login_button'))

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <svg class="mx-auto h-12 w-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">{{ __('messages.login_title') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ __('messages.login_subtitle') }}</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white shadow-md rounded-lg px-6 py-8">
            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Username Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.username') }}</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm" 
                            placeholder="{{ __('messages.username_placeholder') }}"
                            required
                            autocomplete="name"
                        >
                    </div>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('messages.password') }}</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm" 
                            placeholder="{{ __('messages.password_placeholder') }}"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember" 
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                            {{ old('remember') ? 'checked' : '' }}  <!-- Optional: preselect if previous input exists -->
                        
                        <label for="remember" class="ml-2 block text-sm text-gray-700">{{ __('messages.remember_me') }}</label>
                    </div>
                </div>
                

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150 ease-in-out"
                    >
                        {{ __('messages.login_button') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Additional Links -->
        {{-- <div class="mt-4 text-center text-sm">
            <p class="text-gray-600">
                {{ __('messages.login_trouble') }} 
                <a href="#" class="font-medium text-primary hover:text-primary-dark">{{ __('messages.contact_support') }}</a>
            </p>
        </div> --}}
    </div>
</div>
@endsection
