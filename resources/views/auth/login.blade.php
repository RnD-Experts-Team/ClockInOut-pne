@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <svg class="mx-auto h-12 w-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">مرحباً بعودتك</h2>
            <p class="mt-2 text-sm text-gray-600">يرجى تسجيل الدخول إلى حسابك</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white shadow-md rounded-lg px-6 py-8">
            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Username Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">اسم المستخدم</label>
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
                            placeholder="أدخل اسم المستخدم"
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
                    <label for="password" class="block text-sm font-medium text-gray-700">كلمة المرور</label>
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
                            placeholder="أدخل كلمة المرور"
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
                        
                        <label for="remember" class="ml-2 block text-sm text-gray-700">تذكرني</label>
                    </div>
                </div>
                

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150 ease-in-out"
                    >
                        تسجيل الدخول
                    </button>
                </div>
            </form>
        </div>

        <!-- Additional Links -->
        {{-- <div class="mt-4 text-center text-sm">
            <p class="text-gray-600">
                هل تواجه مشكلة في تسجيل الدخول؟ 
                <a href="#" class="font-medium text-primary hover:text-primary-dark">تواصل مع الدعم</a>
            </p>
        </div> --}}
    </div>
</div>
@endsection
