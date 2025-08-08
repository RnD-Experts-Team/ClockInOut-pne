{{-- resources/views/leases/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Lease Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Lease Details - {{ $lease->store_number }}</h1>
            <p class="mt-2 text-sm text-gray-700">{{ $lease->name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('leases.edit', $lease) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Lease
            </a>
            <a href="{{ route('leases.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Store Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                    </svg>
                    Store Information
                </h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Store Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->store_number ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Store Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->name ?? 'N/A' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Store Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->store_address ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">SQF</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->sqf ? number_format($lease->sqf) . ' SQF' : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">HVAC</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $lease->hvac ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $lease->hvac ? 'Available' : 'Not Available' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Financial Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                    Financial Details
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">AWS</dt>
                        <dd class="mt-1 text-lg font-semibold text-blue-600">${{ number_format($lease->aws ?? 0, 2) }}</dd>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Base Rent</dt>
                        <dd class="mt-1 text-lg font-semibold text-green-600">${{ number_format($lease->base_rent ?? 0, 2) }}</dd>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">CAM</dt>
                        <dd class="mt-1 text-lg font-semibold text-purple-600">${{ number_format($lease->cam ?? 0, 2) }}</dd>
                    </div>
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Insurance</dt>
                        <dd class="mt-1 text-lg font-semibold text-orange-600">${{ number_format($lease->insurance ?? 0, 2) }}</dd>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">RE Taxes</dt>
                        <dd class="mt-1 text-lg font-semibold text-red-600">${{ number_format($lease->re_taxes ?? 0, 2) }}</dd>
                    </div>
                    <div class="text-center p-4 bg-teal-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Others</dt>
                        <dd class="mt-1 text-lg font-semibold text-teal-600">${{ number_format($lease->others ?? 0, 2) }}</dd>
                    </div>
                </div>
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-gray-900">Total Monthly Rent:</span>
                        <span class="text-xl font-bold text-green-600">${{ number_format($lease->total_rent, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Term Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lease Terms & Expiration Dates
                </h2>
                @if($lease->term_expiration_dates->count() > 0)
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Renewal Options</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $lease->renewal_options ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Terms Available</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $lease->term_expiration_dates->count() }} terms</dd>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">All Term Expiration Dates</h3>
                            <div class="space-y-2">
                                @foreach($lease->term_expiration_dates as $term)
                                    <div class="flex justify-between items-center p-3 {{ $term['is_initial'] ? 'bg-blue-50 border-l-4 border-blue-400' : 'bg-gray-50' }} rounded">
                                        <span class="font-medium {{ $term['is_initial'] ? 'text-blue-900' : 'text-gray-900' }}">{{ $term['term'] }}</span>
                                        <span class="text-sm {{ $term['is_initial'] ? 'text-blue-700' : 'text-gray-600' }}">{{ $term['expiration_date']->format('M d, Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500">No term information available</p>
                @endif
            </div>

            <!-- Landlord Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Landlord Information
                </h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Landlord Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->landlord_name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($lease->landlord_email)
                                <a href="mailto:{{ $lease->landlord_email }}" class="text-blue-600 hover:text-blue-800">{{ $lease->landlord_email }}</a>
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($lease->landlord_phone)
                                <a href="tel:{{ $lease->landlord_phone }}" class="text-blue-600 hover:text-blue-800">{{ $lease->landlord_phone }}</a>
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Landlord Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->landlord_address ?? 'N/A' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Landlord Responsibilities</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $lease->landlord_responsibility ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Comments -->
            @if($lease->comments)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1.586z" />
                        </svg>
                        Comments
                    </h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $lease->comments }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar with Key Metrics -->
        <div class="space-y-6">
            <!-- Current Status -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Status</h2>
                <div class="space-y-4">
                    @php
                        $currentTerm = $lease->current_term_info;
                        $timeUntilLastTerm = $lease->time_until_last_term_ends;
                        $leaseToSalesRatio = $lease->lease_to_sales_ratio;
                        $timeUntilFranchiseExpires = $lease->time_until_franchise_expires;
                    @endphp
                    
                    @if($currentTerm)
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-900">Current Term</h3>
                            <p class="text-lg font-semibold text-blue-700">{{ $currentTerm['term_name'] }}</p>
                            <p class="text-sm text-blue-600">
                                {{ $currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted'] . ' remaining' }}
                            </p>
                            <p class="text-xs text-blue-500">Until: {{ $currentTerm['expiration_date']->format('M d, Y') }}</p>
                        </div>
                    @endif

                    @if($timeUntilLastTerm)
                        <div class="p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-sm font-medium text-purple-900">Last Term Ends</h3>
                            <p class="text-lg font-semibold {{ $timeUntilLastTerm['expired'] ? 'text-red-700' : 'text-purple-700' }}">
                                {{ $timeUntilLastTerm['expired'] ? 'Expired' : $timeUntilLastTerm['formatted'] }}
                            </p>
                            @if($lease->last_term_expiration_date)
                                <p class="text-xs text-purple-500">{{ $lease->last_term_expiration_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    @endif

                    @if($leaseToSalesRatio)
                        <div class="p-4 {{ $leaseToSalesRatio > 0.15 ? 'bg-red-50' : ($leaseToSalesRatio > 0.10 ? 'bg-orange-50' : 'bg-green-50') }} rounded-lg">
                            <h3 class="text-sm font-medium {{ $leaseToSalesRatio > 0.15 ? 'text-red-900' : ($leaseToSalesRatio > 0.10 ? 'text-orange-900' : 'text-green-900') }}">Lease to Sales Ratio</h3>
                            <p class="text-lg font-semibold {{ $leaseToSalesRatio > 0.15 ? 'text-red-700' : ($leaseToSalesRatio > 0.10 ? 'text-orange-700' : 'text-green-700') }}">
                                {{ number_format($leaseToSalesRatio * 100, 2) }}%
                            </p>
                            <p class="text-xs {{ $leaseToSalesRatio > 0.15 ? 'text-red-500' : ($leaseToSalesRatio > 0.10 ? 'text-orange-500' : 'text-green-500') }}">
                                Annual Rent: ${{ number_format($lease->total_rent * 12, 0) }}<br>
                                Annual Sales: ${{ number_format($lease->aws * 4, 0) }}
                            </p>
                        </div>
                    @endif

                    @if($timeUntilFranchiseExpires)
                        <div class="p-4 bg-yellow-50 rounded-lg">
                            <h3 class="text-sm font-medium text-yellow-900">Franchise Agreement</h3>
                            <p class="text-lg font-semibold {{ $timeUntilFranchiseExpires['expired'] ? 'text-red-700' : 'text-yellow-700' }}">
                                {{ $timeUntilFranchiseExpires['expired'] ? 'Expired' : $timeUntilFranchiseExpires['formatted'] . ' remaining' }}
                            </p>
                            @if($lease->franchise_agreement_expiration_date)
                                <p class="text-xs text-yellow-600">Until: {{ $lease->franchise_agreement_expiration_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Additional Details -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Details</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">% Increase Per Year</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->percent_increase_per_year ? $lease->percent_increase_per_year . '%' : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Security Deposit</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($lease->security_deposit ?? 0, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Initial Lease Expiration</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->initial_lease_expiration_date?->format('M d, Y') ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Franchise Agreement Expiration</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $lease->franchise_agreement_expiration_date?->format('M d, Y') ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Delete Action -->
            <div class="bg-white shadow-sm ring-1 ring-red-200 rounded-lg p-6 border-red-200">
                <h2 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    Danger Zone
                </h2>
                <p class="text-sm text-red-700 mb-4">This action cannot be undone. This will permanently delete the lease record.</p>
                <form action="{{ route('leases.destroy', $lease) }}" 
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this lease? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Lease
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
