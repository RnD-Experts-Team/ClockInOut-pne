
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Cost Breakdown Analysis</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Cost Breakdown Analysis</h1>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                        <tr class="bg-[#3B82F6] text-white">
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Store #</th>
                            <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold">Store Name</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">AWS</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">Total Rent</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">L2S Ratio</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">Base Rent</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">% Increase</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">Insurance</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">CAM</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">RE Taxes</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">Others</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">Security Deposit</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($leases as $index => $lease)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50">
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $lease->store_number ?: 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm">{{ $lease->name ?: 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->aws ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->total_rent ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">{{ $lease->lease_to_sales_ratio ? number_format($lease->lease_to_sales_ratio * 100, 2) . '%' : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->base_rent ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">{{ $lease->percent_increase_per_year ? number_format($lease->percent_increase_per_year, 1) . '%' : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->insurance ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->cam ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->re_taxes ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->others ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->security_deposit ?: 0, 0) }}</td>
                            </tr>
                        @endforeach
                        </tbody>

                        <!-- Totals Row -->
                        <tfoot>
                        <tr class="bg-[#2563EB] text-white font-semibold">
                            <td class="border border-gray-300 px-3 py-3 text-sm text-center">TOTAL</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm">{{ $leases->count() }} Stores</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('aws'), 0) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum(fn($lease) => $lease->total_rent), 0) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">
                                @php
                                    $totalAws = $leases->sum('aws');
                                    $totalRent = $leases->sum(fn($lease) => $lease->total_rent);
                                    $totalRatio = $totalAws > 0 ? ($totalRent * 12) / ($totalAws * 4) : 0;
                                @endphp
                                {{ number_format($totalRatio * 100, 2) }}%
                            </td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('base_rent'), 0) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">Avg</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('insurance'), 0) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('cam'), 0) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('re_taxes'), 0) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('others'), 0) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('security_deposit'), 0) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('costBreakdownModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#3B82F6] text-white text-sm font-medium rounded hover:bg-[#2563EB]">
                    Close
                </button>
            </div>
        </div>
    </div>

