<div id="apartmentLeaseDashboard" class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Apartment Lease List</h1>
            <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <!-- Compact Table Design -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Apartment Lease List</h1>

            <table class="w-full border-collapse">
                <thead>
                <tr class="bg-[#3B82F6] text-white">
                    <th class="border border-gray-300 px-2 py-2 text-center text-xs font-semibold w-12">Store #</th>
                    <th class="border border-gray-300 px-2 py-2 text-left text-xs font-semibold">Address</th>
                    <th class="border border-gray-300 px-2 py-2 text-right text-xs font-semibold w-20">Total Rent</th>
                    <th class="border border-gray-300 px-2 py-2 text-right text-xs font-semibold w-20">AT Count</th>
                    <th class="border border-gray-300 px-2 py-2 text-left text-xs font-semibold">Lease Holder</th>
                    <th class="border border-gray-300 px-2 py-2 text-center text-xs font-semibold w-24">Expires</th>
                    <th class="border border-gray-300 px-2 py-2 text-center text-xs font-semibold w-20">Family</th>
                    <th class="border border-gray-300 px-2 py-2 text-center text-xs font-semibold w-16">Cars</th>
                </tr>
                </thead>
                <tbody>
                @foreach($leases as $index => $lease)
                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50">
                        <td class="border border-gray-300 px-2 py-1 text-xs text-center">{{ $lease->store_number ?: 'N/A' }}</td>
                        <td class="border border-gray-300 px-2 py-1 text-xs whitespace-normal">{{ $lease->apartment_address ?: 'N/A' }}</td>
                        <td class="border border-gray-300 px-2 py-1 text-xs text-right">${{ number_format($lease->total_rent ?: 0, 0) }}</td>
                        <td class="border border-gray-300 px-2 py-1 text-xs text-right">{{ $lease->number_of_AT ?: 'N/A' }}</td>
                        <td class="border border-gray-300 px-2 py-1 text-xs whitespace-normal">{{ $lease->lease_holder ?: 'N/A' }}</td>
                        <td class="border border-gray-300 px-2 py-1 text-xs text-center">{{ $lease->expiration_date ? \Carbon\Carbon::parse($lease->expiration_date)->format('M j, Y') : 'N/A' }}</td>
                        <td class="border border-gray-300 px-2 py-1 text-xs text-center">{{ $lease->is_family === 'Yes' || $lease->is_family === 'yes' ? 'Yes' : 'No' }}</td>
                        <td class="border border-gray-300 px-2 py-1 text-xs text-center">{{ $lease->has_car > 0 ? $lease->has_car : '0' }}</td>
                    </tr>
                @endforeach
                </tbody>

                <!-- Totals Row -->
                <tfoot>
                <tr class="bg-[#2563EB] text-white font-semibold">
                    <td class="border border-gray-300 px-2 py-1 text-xs text-center">TOTAL</td>
                    <td class="border border-gray-300 px-2 py-1 text-xs">{{ $leases->count() }} Apartments</td>
                    <td class="border border-gray-300 px-2 py-1 text-xs text-right">${{ number_format($leases->sum(fn($lease) => $lease->total_rent), 0) }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-xs text-right">{{ $leases->sum('number_of_AT') }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-xs"></td>
                    <td class="border border-gray-300 px-2 py-1 text-xs text-center"></td>
                    <td class="border border-gray-300 px-2 py-1 text-xs text-center">{{ $leases->where('is_family', 'Yes')->count() }} Yes</td>
                    <td class="border border-gray-300 px-2 py-1 text-xs text-center">{{ $leases->sum('has_car') }}</td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
