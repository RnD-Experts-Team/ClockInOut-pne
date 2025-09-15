{{-- resources/views/stores/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'Edit Store')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Store</h1>
                    <p class="mt-2 text-sm text-gray-700">Update store information for <span class="font-semibold text-primary">#{{ $store->store_number }}</span></p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('stores.show', $store) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Store
                    </a>
                    <a href="{{ route('stores.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Stores
                    </a>
                </div>
            </div>
        </div>

        <!-- Current Store Info Banner -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Current Store Information</h3>
                    <div class="mt-1 text-sm text-blue-700">
                        <p><strong>Store Number:</strong> {{ $store->store_number }}</p>
                        <p><strong>Name:</strong> {{ $store->name ?: 'No name set' }}</p>
                        <p><strong>Status:</strong>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $store->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $store->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        </p>
                        @if($store->address)
                            <p><strong>Address:</strong> {{ Str::limit($store->address, 100) }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
            <div class="px-6 py-6">
                <form method="POST" action="{{ route('stores.update', $store) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

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
                                       value="{{ old('store_number', $store->store_number) }}"
                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary sm:text-sm @error('store_number') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
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
                                       value="{{ old('name', $store->name) }}"
                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary sm:text-sm @error('name') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
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
                                      class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary sm:text-sm @error('address') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                      placeholder="Enter the complete address including street, city, state, and postal code...">{{ old('address', $store->address) }}</textarea>
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
                                       class="focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
                                    {{ old('is_active', $store->is_active) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_active" class="font-medium text-gray-700">Store is Active</label>
                                <p class="text-gray-500">When checked, this store will be available for operations and can receive maintenance requests.</p>
                                @if(!$store->is_active)
                                    <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-yellow-800 text-xs">
                                        <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        This store is currently inactive and won't appear in system dropdowns.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <a href="{{ route('stores.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </a>

                        <button type="submit"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 hover:shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Store
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Related Data Warning (if applicable) -->
        @if($store->maintenance_requests_count > 0 || $store->leases_count > 0 || $store->payments_count > 0)
            <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800">Related Records Found</h3>
                        <div class="mt-2 text-sm text-amber-700">
                            <p>This store has related records that may be affected by changes:</p>
                            <ul class="list-disc pl-5 mt-1 space-y-1">
                                @if($store->maintenance_requests_count > 0)
                                    <li>{{ $store->maintenance_requests_count }} maintenance request(s)</li>
                                @endif
                                @if($store->leases_count > 0)
                                    <li>{{ $store->leases_count }} lease(s)</li>
                                @endif
                                @if($store->payments_count > 0)
                                    <li>{{ $store->payments_count }} payment record(s)</li>
                                @endif
                            </ul>
                            <p class="mt-2 font-medium">Consider the impact on these records before making significant changes, especially to the store number.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Change Log (if you want to track changes) -->
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-center text-sm text-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><strong>Created:</strong> {{ $store->created_at->format('M d, Y g:i A') }}</span>
                @if($store->updated_at->ne($store->created_at))
                    <span class="ml-4"><strong>Last Updated:</strong> {{ $store->updated_at->format('M d, Y g:i A') }}</span>
                @endif
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
