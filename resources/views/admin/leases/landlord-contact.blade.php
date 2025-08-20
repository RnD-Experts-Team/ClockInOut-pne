@extends('layouts.app')

@section('title', 'Landlord Contact Directory')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Landlord Contact Directory</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse" id="landlordDirectoryTable">
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(0, 'text')" id="landlord-header-0">
                                <div class="flex items-center justify-between">
                                    Store #
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-0">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(1, 'text')" id="landlord-header-1">
                                <div class="flex items-center justify-between">
                                    Store Name
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-1">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(2, 'text')" id="landlord-header-2">
                                <div class="flex items-center justify-between">
                                    Landlord Name
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-2">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(3, 'text')" id="landlord-header-3">
                                <div class="flex items-center justify-between">
                                    Email
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-3">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(4, 'text')" id="landlord-header-4">
                                <div class="flex items-center justify-between">
                                    Phone
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-4">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(5, 'text')" id="landlord-header-5">
                                <div class="flex items-center justify-between">
                                    Address
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-5">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(6, 'number')" id="landlord-header-6">
                                <div class="flex items-center justify-end">
                                    AWS
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-6">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(7, 'number')" id="landlord-header-7">
                                <div class="flex items-center justify-end">
                                    Total Rent
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-7">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-5 py-5 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(8, 'text')" id="landlord-header-8">
                                <div class="flex items-center justify-between">
                                    Responsibilities
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-8">A↓</span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="landlordTableBody">
                        @foreach($leases as $index => $lease)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-[#fff4ed]">
                                <!-- Store Number -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->store_number ?: 'N/A' }}">
                                    {{ $lease->store_number ?: 'N/A' }}
                                </td>
                                <!-- Store Name -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->name ?: 'N/A' }}">
                                    {{ $lease->name ?: 'N/A' }}
                                </td>
                                <!-- Landlord Name -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->landlord_name ?: 'N/A' }}">
                                    {{ $lease->landlord_name ?: 'N/A' }}
                                </td>
                                <!-- Email -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->landlord_email ?: 'N/A' }}">
                                    {{ $lease->landlord_email ?: 'N/A' }}
                                </td>
                                <!-- Phone -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->landlord_phone ?: 'N/A' }}">
                                    {{ $lease->landlord_phone ?: 'N/A' }}
                                </td>
                                <!-- Address -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->landlord_address ?: 'N/A' }}">
                                    {{ $lease->landlord_address ?: 'N/A' }}
                                </td>
                                <!-- AWS -->
                                <td class="border border-gray-300 px-4 py-3 text-sm text-right" data-sort="{{ $lease->aws ?: 0 }}">
                                    ${{ number_format($lease->aws ?: 0, 0) }}
                                </td>
                                <!-- Total Rent -->
                                <td class="border border-gray-300 px-4 py-3 text-sm text-right" data-sort="{{ $lease->total_rent ?: 0 }}">
                                    ${{ number_format($lease->total_rent ?: 0, 0) }}
                                </td>
                                <!-- Responsibilities -->
                                <td class="border border-gray-300 px-5 py-5 text-sm" data-sort="{{ $lease->landlord_responsibility ?: 'N/A' }}">
                                    {{ $lease->landlord_responsibility ?: 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <!-- Totals Row -->
                        <tfoot>
                        <tr class="bg-[#e55b17] text-white font-semibold">
                            <td class="border border-gray-300 px-4 py-3 text-sm">TOTAL</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">{{ $leases->count() }} Stores</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">{{ $leases->whereNotNull('landlord_name')->unique('landlord_name')->count() }} Landlords</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">-</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">-</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">-</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm text-right">${{ number_format($leases->sum('aws'), 0) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm text-right">${{ number_format($leases->sum(fn($lease) => $lease->total_rent), 0) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">-</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('landlordContactModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#ff671b] text-white text-sm font-medium rounded hover:bg-[#e55b17]">
                    Close
                </button>
            </div>
        </div>
    </div>


@endsection
