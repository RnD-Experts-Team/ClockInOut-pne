@extends('layouts.app')

@section('title', 'Create New Store')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create New Store</h1>
                    <p class="mt-2 text-sm text-gray-700">Add a new store location to your system</p>
                </div>
                <a href="{{ route('stores.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff671b] transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Stores
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
            <div class="px-6 py-6">
                <form method="POST" action="{{ route('stores.store') }}" class="space-y-6">
                    @csrf

                    <!-- Store Number and Name Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Store Number -->
                        <div>
                            <label for="store_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Store Number <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                </div>
                                <input type="text"
                                       id="store_number"
                                       name="store_number"
                                       value="{{ old('store_number') }}"
                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-[#ff671b] focus:border-[#ff671b] sm:text-sm @error('store_number') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                       placeholder="e.g., 001, ST-001"
                                       required>
                            </div>
                            @error('store_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Unique identifier for the store</p>
                        </div>

                        <!-- Store Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Store Name
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                                    </svg>
                                </div>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-[#ff671b] focus:border-[#ff671b] sm:text-sm @error('name') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                       placeholder="e.g., Downtown Store, Mall Location">
                            </div>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Optional display name for the store</p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <div class="relative">
                            <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <textarea id="address"
                                      name="address"
                                      rows="3"
                                      class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-[#ff671b] focus:border-[#ff671b] sm:text-sm @error('address') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                      placeholder="Enter the complete address including street, city, state, and postal code...">{{ old('address') }}</textarea>
                        </div>
                        @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Physical location of the store</p>
                    </div>

                    <!-- Status Toggle -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_active"
                                       name="is_active"
                                       type="checkbox"
                                       value="1"
                                       class="focus:ring-[#ff671b] h-4 w-4 text-[#ff671b] border-gray-300 rounded"
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_active" class="font-medium text-gray-700">Store is Active</label>
                                <p class="text-gray-500">When checked, this store will be available for operations and can receive maintenance requests.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <a href="{{ route('stores.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff671b] transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </a>

                        <button type="submit"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-[#ff671b] hover:bg-[#e55b17] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff671b] transition-all duration-200 hover:shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Create Store
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Store Creation Guidelines</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li><strong>Store Number:</strong> Must be unique across all stores. Use a consistent format (e.g., 001, 002 or ST-001, ST-002).</li>
                            <li><strong>Store Name:</strong> Optional but recommended for easier identification in reports and lists.</li>
                            <li><strong>Address:</strong> Include complete address for better location tracking and maintenance scheduling.</li>
                            <li><strong>Active Status:</strong> Inactive stores won't appear in dropdown menus for new maintenance requests.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-format store number as user types
        document.getElementById('store_number').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            // Remove any non-alphanumeric characters except hyphens
            value = value.replace(/[^A-Z0-9\-]/g, '');
            e.target.value = value;
        });

        // Auto-capitalize store name
        document.getElementById('name').addEventListener('blur', function(e) {
            if (e.target.value) {
                e.target.value = e.target.value
                    .split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            }
        });
    </script>
@endsection
