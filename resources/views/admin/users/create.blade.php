@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                <h1 class="text-2xl font-semibold text-white">Add New User</h1>
                <p class="mt-1 text-primary-100">Create a new user account</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
            <div class="px-6 py-4 bg-red-50 border-l-4 border-red-500">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
            <form action="{{ route('users.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   class="block w-full pl-10 pr-3 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md transition-shadow"
                                   placeholder="Enter user name" required>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                class="block w-full pl-10 pr-3 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md transition-shadow"
                                placeholder="Enter email address" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="password" id="password" name="password"
                                   class="block w-full pl-10 pr-3 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md transition-shadow"
                                   placeholder="Enter password" required>
                        </div>
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="role" name="role"
                                    class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <!-- Hourly Pay -->
                    <div>
                        <label for="hourly_pay" class="block text-sm font-medium text-gray-700">Hourly Pay</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" step="0.01" id="hourly_pay" name="hourly_pay" value="{{ old('hourly_pay') }}"
                                   class="block w-full pl-10 pr-3 py-3 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md transition-shadow"
                                   placeholder="Enter hourly pay rate" required>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end pt-6">
                    <a href="{{ route('users.index') }}" class="mr-4 text-sm font-medium text-gray-600 hover:text-gray-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-primary text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
