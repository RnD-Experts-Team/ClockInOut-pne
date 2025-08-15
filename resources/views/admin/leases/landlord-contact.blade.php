

@section('title', 'Landlord Contact Directory')


    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Landlord Contact Directory</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden ce">
                <div class="overflow-x-auto">
                    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Landlord Contact Directory</h1>
                    <table class="min-w-full border-collapse">
                        <thead>
                        <tr class="bg-[#3B82F6] text-white">
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold">Store #</th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold">Store Name</th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold">Landlord Name</th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold">Email</th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold">Phone</th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold">Address</th>
                            <th class="border border-gray-300 px-4 py-3 text-right text-sm font-semibold">AWS</th>
                            <th class="border border-gray-300 px-4 py-3 text-right text-sm font-semibold">Total Rent</th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold">Responsibilities</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($leases as $index => $lease)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50">
                                <!-- Store Number -->
                                <td class="border border-gray-300 px-4 py-3 text-sm">
                                    {{ $lease->store_number ?: 'N/A' }}
                                </td>

                                <!-- Store Name -->
                                <td class="border border-gray-300 px-4 py-3 text-sm">
                                    {{ $lease->name ?: 'N/A' }}
                                </td>

                                <!-- Landlord Name -->
                                <td class="border border-gray-300 px-4 py-3 text-sm">
                                    {{ $lease->landlord_name ?: 'N/A' }}
                                </td>

                                <!-- Email -->
                                <td class="border border-gray-300 px-4 py-3 text-sm">
                                    {{ $lease->landlord_email ?: 'N/A' }}
                                </td>

                                <!-- Phone -->
                                <td class="border border-gray-300 px-4 py-3 text-sm">
                                    {{ $lease->landlord_phone ?: 'N/A' }}
                                </td>

                                <!-- Address -->
                                <td class="border border-gray-300 px-4 py-3 text-sm">
                                    {{ $lease->landlord_address ?: 'N/A' }}
                                </td>

                                <!-- AWS -->
                                <td class="border border-gray-300 px-4 py-3 text-sm text-right">
                                    ${{ number_format($lease->aws ?: 0, 0) }}
                                </td>

                                <!-- Total Rent -->
                                <td class="border border-gray-300 px-4 py-3 text-sm text-right">
                                    ${{ number_format($lease->total_rent ?: 0, 0) }}
                                </td>

                                <!-- Responsibilities -->
                                <td class="border border-gray-300 px-4 py-3 text-sm">
                                    {{ $lease->landlord_responsibility ?: 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                        <!-- Totals Row -->
                        <tfoot>
                        <tr class="bg-[#2563EB] text-white font-semibold">
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
                        class="inline-flex items-center px-4 py-2 bg-[#3B82F6] text-white text-sm font-medium rounded hover:bg-[#2563EB]">
                    Close
                </button>
            </div>
        </div>
    </div>
