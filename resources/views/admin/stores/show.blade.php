{{-- resources/views/stores/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Store Details')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Store Details</h1>
                    <p class="mt-2 text-sm text-gray-700">Complete information for store <span class="font-semibold text-primary">#{{ $store->store_number }}</span></p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('stores.edit', $store) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Store
                    </a>
                    <a href="{{ route('stores.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Store Information Card -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg mb-8">
            <div class="px-6 py-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $store->name ?: 'Store #' . $store->store_number }}</h2>
                        <p class="text-sm text-gray-500">Store Number: {{ $store->store_number }}</p>
                    </div>
                    <div class="ml-auto">
                        @if($store->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3"/>
                            </svg>
                            Active
                        </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3"/>
                            </svg>
                            Inactive
                        </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Store Number</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $store->store_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Store Name</dt>
                                <dd class="text-sm text-gray-900">{{ $store->name ?: 'Not specified' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="text-sm">
                                    @if($store->is_active)
                                        <span class="text-green-600 font-medium">Active</span>
                                    @else
                                        <span class="text-red-600 font-medium">Inactive</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Address Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Location</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="text-sm text-gray-900">
                                    @if($store->address)
                                        {{ $store->address }}
                                    @else
                                        <span class="text-gray-400 italic">Not specified</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Timestamps -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">System Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="text-sm text-gray-900">{{ $store->created_at->format('M d, Y g:i A') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="text-sm text-gray-900">{{ $store->updated_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Maintenance Requests</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $store->maintenanceRequests ? $store->maintenanceRequests->count() : 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Store Leases</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $store->leases ? $store->leases->count() : 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Apartment Leases</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $store->apartmentLeases ? $store->apartmentLeases->count() : 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Payment Records</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $store->payments ? $store->payments->count() : 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Records -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Maintenance Requests -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Recent Maintenance Requests</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $store->maintenanceRequests ? $store->maintenanceRequests->count() : 0 }} total
                    </span>
                    </div>
                </div>
                <div class="px-6 py-4">
                    @if($store->maintenanceRequests && $store->maintenanceRequests->count() > 0)
                        <div class="space-y-4">
                            @foreach($store->maintenanceRequests->take(5) as $request)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $request->form_id ?? 'Request #' . $request->id }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ Str::limit($request->description_of_issue ?? 'No description', 60) }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $request->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        @switch($request->status)
                                            @case('done')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Done
                                            </span>
                                                @break
                                            @case('in_progress')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                In Progress
                                            </span>
                                                @break
                                            @case('on_hold')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                On Hold
                                            </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                            </span>
                                        @endswitch
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($store->maintenanceRequests->count() > 5)
                            <div class="text-center mt-4">
                                <p class="text-sm text-gray-500">and {{ $store->maintenanceRequests->count() - 5 }} more requests...</p>
                                <a href="{{ route('maintenance-requests.index', ['store_id' => $store->id]) }}"
                                   class="inline-flex items-center mt-2 text-sm font-medium text-primary hover:text-primary-dark">
                                    View all maintenance requests
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No maintenance requests</h3>
                            <p class="mt-1 text-sm text-gray-500">This store has no maintenance requests yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Recent Payments</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $store->payments ? $store->payments->count() : 0 }} total
                    </span>
                    </div>
                </div>
                <div class="px-6 py-4">
                    @if($store->payments && $store->payments->count() > 0)
                        <div class="space-y-4">
                            @foreach($store->payments->take(5) as $payment)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">${{ number_format($payment->cost, 2) }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ $payment->what_got_fixed ?: 'Payment record' }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $payment->date ? $payment->date->format('M d, Y') : 'No date' }}</p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        @if(isset($payment->paid))
                                            @if($payment->paid)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Paid
                                            </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Unknown
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($store->payments->count() > 5)
                            <div class="text-center mt-4">
                                <p class="text-sm text-gray-500">and {{ $store->payments->count() - 5 }} more payments...</p>
                                <a href="{{ route('payments.index', ['store' => $store->store_number]) }}"
                                   class="inline-flex items-center mt-2 text-sm font-medium text-primary hover:text-primary-dark">
                                    View all payments
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No payment records</h3>
                            <p class="mt-1 text-sm text-gray-500">This store has no payment records yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Leases Section -->
            @if($store->leases && $store->leases->count() > 0)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Store Leases</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ $store->leases->count() }} total
                    </span>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            @foreach($store->leases as $lease)
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <p class="text-sm font-medium text-gray-900">{{ $lease->name ?: 'Store Lease' }}</p>
                                    <div class="text-sm text-gray-500 mt-1">
                                        @if(isset($lease->base_rent))
                                            <span>Base Rent: ${{ number_format($lease->base_rent, 2) }}</span>
                                        @endif
                                        @if(isset($lease->initial_lease_expiration_date))
                                            <span class="ml-2">Expires: {{ $lease->initial_lease_expiration_date->format('M d, Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Apartment Leases Section -->
            @if($store->apartmentLeases && $store->apartmentLeases->count() > 0)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Apartment Leases</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        {{ $store->apartmentLeases->count() }} total
                    </span>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            @foreach($store->apartmentLeases as $apartment)
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <p class="text-sm font-medium text-gray-900">{{ $apartment->lease_holder }}</p>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <span>Rent: ${{ number_format($apartment->rent, 2) }}</span>
                                        @if(isset($apartment->expiration_date))
                                            <span class="ml-2">Expires: {{ $apartment->expiration_date->format('M d, Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
