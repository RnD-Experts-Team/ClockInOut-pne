@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                <h1 class="text-2xl font-semibold text-white">Edit User</h1>
                <p class="mt-1 text-primary-100">Update user information</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
            <div class="px-6 py-4 bg-red-50 border-l-4 border-red-500">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Form -->
            <form action="{{ route('users.update', $user->id) }}" method="POST" class="px-6 py-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                   class="block w-full pl-10 pr-3 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md transition-shadow @error('name') border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                   placeholder="Enter your full name">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="block w-full pl-10 pr-3 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md transition-shadow @error('email') border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                   placeholder="Enter your email address">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="role" name="role"
                                    class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md">
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        @error('role')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="text-sm text-gray-500 mb-4">
                            Leave password fields empty if you don't want to change the current password
                        </div>
                        <div class="space-y-6">
                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input type="password" id="password" name="password"
                                           class="block w-full pl-10 pr-3 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md transition-shadow @error('password') border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                           placeholder="Enter new password">
                                </div>
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Confirm New Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                           class="block w-full pl-10 pr-3 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md transition-shadow"
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end pt-6">
                    <a href="{{ route('users.index') }}" class="mr-4 text-sm font-medium text-gray-600 hover:text-gray-500 p-8">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-primary text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
