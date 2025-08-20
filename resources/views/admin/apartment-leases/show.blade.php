@extends('layouts.app')

@section('title', 'Apartment Lease Details')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">
                    Apartment Lease Details
                    @if($apartmentLease->store_number)
                        - Store #{{ $apartmentLease->store_number }}
                    @endif
                </h1>
                <p class="mt-2 text-sm text-black-700">{{ Str::limit($apartmentLease->apartment_address, 80) }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.apartment-leases.edit', $apartmentLease) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Lease
                </a>
                <a href="{{ route('admin.apartment-leases.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-orange-200 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <!-- Status Alert -->
        @if($apartmentLease->expiration_date)
            @php
                $expirationDate = \Carbon\Carbon::parse($apartmentLease->expiration_date);
                $now = \Carbon\Carbon::now();
                $isExpired = $expirationDate->isPast();
                $showAlert = $isExpired || $now->diffInDays($expirationDate, false) <= 30;
            @endphp

            @if($showAlert)
                <div class="mb-6 p-4 rounded-lg {{ $isExpired ? 'bg-orange-100 text-black-800' : 'bg-orange-100 text-black-800' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">
                            @php
                                $interval = $now->diff($expirationDate);
                                $timeString = '';

                                if ($interval->y > 0) {
                                    $timeString .= $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ';
                                }
                                if ($interval->m > 0) {
                                    $timeString .= $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ';
                                }
                                if ($interval->d > 0) {
                                    $timeString .= $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ';
                                }
                            @endphp

                            @if($isExpired)
                                Expired {{ trim($timeString) }} ago
                            @else
                                Expires in {{ trim($timeString) }}
                            @endif
                        </span>
                    </div>
                </div>
            @endif
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Apartment Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                        </svg>
                        Apartment Information
                    </h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($apartmentLease->store_number)
                            <div>
                                <dt class="text-sm font-medium text-black-500">Store Number</dt>
                                <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->store_number }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-black-500">Drive Time</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->drive_time ?? 'N/A' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-black-500">Apartment Address</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->apartment_address }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Financial Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        Financial Details
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="text-center p-4 bg-orange-50 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Monthly Rent</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($apartmentLease->rent, 2) }}</dd>
                        </div>
                        @if($apartmentLease->utilities)
                            <div class="text-center p-4 bg-orange-50 rounded-lg">
                                <dt class="text-sm font-medium text-black-500">Monthly Utilities</dt>
                                <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($apartmentLease->utilities, 2) }}</dd>
                            </div>
                        @endif
                    </div>
                    <div class="mt-6 p-4 bg-orange-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-black-900">Total Monthly Cost:</span>
                            <span class="text-xl font-bold text-black-600">${{ number_format($apartmentLease->total_rent, 2) }}</span>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-orange-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-black-900">Annual Cost:</span>
                            <span class="text-xl font-bold text-black-600">${{ number_format($apartmentLease->total_rent * 12, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Resident Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Resident & Property Details
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-orange-50 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Number of AT</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">{{ $apartmentLease->number_of_AT }}</dd>
                        </div>
                        <div class="text-center p-4 bg-orange-50 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Number of Cars</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">{{ $apartmentLease->has_car }}</dd>
                        </div>
                        <div class="text-center p-4 {{ $apartmentLease->is_family === 'Yes' || $apartmentLease->is_family === 'yes' ? 'bg-orange-50' : 'bg-orange-50' }} rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Family Unit</dt>
                            <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $apartmentLease->is_family === 'Yes' || $apartmentLease->is_family === 'yes' ? 'bg-orange-100 text-black-800' : 'bg-orange-100 text-black-800' }}">
                                {{ ($apartmentLease->is_family === 'Yes' || $apartmentLease->is_family === 'yes') ? 'Yes' : 'No' }}
                            </span>
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Lease Holder Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Lease Holder Information
                    </h2>
                    <dl class="grid grid-cols-1 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-black-500">Lease Holder(s)</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->lease_holder }}</dd>
                        </div>
                        @if($apartmentLease->expiration_date)
                            <div>
                                <dt class="text-sm font-medium text-black-500">Lease Expiration Date</dt>
                                <dd class="mt-1 text-sm text-black-900">
                                    @php
                                        $expirationDate = \Carbon\Carbon::parse($apartmentLease->expiration_date);
                                        $now = \Carbon\Carbon::now();

                                        if ($expirationDate->isPast()) {
                                            $interval = $now->diff($expirationDate);
                                            $timeAgo = '';

                                            if ($interval->y > 0) {
                                                $timeAgo .= $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ';
                                            }
                                            if ($interval->m > 0) {
                                                $timeAgo .= $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ';
                                            }
                                            if ($interval->d > 0) {
                                                $timeAgo .= $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ';
                                            }

                                            $formattedDate = $expirationDate->format('F j, Y') . ' (' . trim($timeAgo) . ' ago)';
                                        } else {
                                            $interval = $now->diff($expirationDate);
                                            $timeFromNow = '';

                                            if ($interval->y > 0) {
                                                $timeFromNow .= $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ';
                                            }
                                            if ($interval->m > 0) {
                                                $timeFromNow .= $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ';
                                            }
                                            if ($interval->d > 0) {
                                                $timeFromNow .= $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ';
                                            }

                                            $formattedDate = $expirationDate->format('F j, Y') . ' (in ' . trim($timeFromNow) . ')';
                                        }
                                    @endphp
                                    {{ $formattedDate }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Notes -->
                @if($apartmentLease->notes)
                    <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1.586z" />
                            </svg>
                            Notes
                        </h2>
                        <div class="bg-orange-50 rounded-lg p-4">
                            <p class="text-sm text-black-900 whitespace-pre-wrap">{{ $apartmentLease->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar with Key Metrics -->
            <div class="space-y-6">
                <!-- Current Status -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4">Current Status</h2>
                    <div class="space-y-4">
                        @if($apartmentLease->expiration_date)
                            @php
                                $expirationDate = \Carbon\Carbon::parse($apartmentLease->expiration_date);
                                $now = \Carbon\Carbon::now();
                                $isExpired = $expirationDate->isPast();
                                $interval = $now->diff($expirationDate);
                                $timeString = '';

                                if ($interval->y > 0) {
                                    $timeString .= $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ';
                                }
                                if ($interval->m > 0) {
                                    $timeString .= $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ';
                                }
                                if ($interval->d > 0) {
                                    $timeString .= $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ';
                                }
                            @endphp

                            @if($isExpired)
                                <div class="p-4 bg-orange-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-black-900">Lease Status</h3>
                                    <p class="text-lg font-semibold text-black-700">
                                        Expired {{ trim($timeString) }} ago
                                    </p>
                                    <p class="text-xs text-black-100">
                                        Expiration: {{ $expirationDate->format('M d, Y') }}
                                    </p>
                                </div>
                            @elseif($now->diffInDays($expirationDate, false) <= 30)
                                <div class="p-4 bg-orange-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-black-900">Lease Status</h3>
                                    <p class="text-lg font-semibold text-black-700">
                                        Expires in {{ trim($timeString) }}
                                    </p>
                                    <p class="text-xs text-black-100">
                                        Expiration: {{ $expirationDate->format('M d, Y') }}
                                    </p>
                                </div>
                            @else
                                <div class="p-4 bg-orange-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-black-900">Lease Status</h3>
                                    <p class="text-lg font-semibold text-black-700">Active</p>
                                    <p class="text-xs text-black-100">
                                        Expires: {{ $expirationDate->format('M d, Y') }}
                                    </p>
                                </div>
                            @endif
                        @else
                            <div class="p-4 bg-orange-50 rounded-lg">
                                <h3 class="text-sm font-medium text-black-900">Lease Status</h3>
                                <p class="text-lg font-semibold text-black-700">No expiration date set</p>
                            </div>
                        @endif

                        <!-- Monthly Rent Per AT -->
                        <div class="p-4 bg-orange-50 rounded-lg">
                            <h3 class="text-sm font-medium text-black-900">Monthly Rent per AT</h3>
                            <p class="text-lg font-semibold text-black-700">
                                ${{ number_format($apartmentLease->total_rent / max($apartmentLease->number_of_AT, 1), 2) }}
                            </p>
                            <p class="text-xs text-black-100">Per AT per month</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('admin.apartment-leases.edit', $apartmentLease) }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Lease
                        </a>
                        <a href="{{ route('admin.apartment-leases.index') }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-orange-200 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            View All Leases
                        </a>
                    </div>
                </div>

                <!-- Record Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4">Record Information</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-black-500">Created By</dt>
                            <dd class="mt-1 text-sm text-black-900">
                                {{ $apartmentLease->creator->name ?? 'System' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Created</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        @if($apartmentLease->created_at != $apartmentLease->updated_at)
                            <div>
                                <dt class="text-sm font-medium text-black-500">Last Modified</dt>
                                <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->updated_at->diffForHumans() }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Delete Action -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-200 rounded-lg p-6 border-orange-200">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        Danger Zone
                    </h2>
                    <p class="text-sm text-black-700 mb-4">This action cannot be undone. This will permanently delete the apartment lease record.</p>
                    <form action="{{ route('admin.apartment-leases.destroy', $apartmentLease) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this apartment lease? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200 font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Apartment Lease
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
