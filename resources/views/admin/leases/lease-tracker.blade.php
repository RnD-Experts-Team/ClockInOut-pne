
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Lease Tracker Dashboard</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Lease Tracker Dashboard</h1>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                        <tr class="bg-[#3B82F6] text-white">
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Store #</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">AWS</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">Total Rent</th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold">L2S Ratio</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Time Left on Current Term</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Total Lease Life</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Term 1 Exp</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Term 2 Exp</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Term 3 Exp</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Term 4 Exp</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Term 5 Exp</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Franchise Exp</th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($leases as $index => $lease)
                            @php
                                $currentTerm = $lease->current_term_info;
                                $termDates = $lease->term_expiration_dates;
                                $timeUntilFranchiseExpires = $lease->time_until_franchise_expires;

                                $isExpired = $currentTerm && $currentTerm['time_left']['expired'];
                                $isWarning = $currentTerm && !$currentTerm['time_left']['expired'] &&
                                    ($currentTerm['time_left']['years'] == 0 && $currentTerm['time_left']['months'] <= 6);

                                $rowClass = '';
                                $status = 'ACTIVE';
                                if ($isExpired) {
                                    $rowClass = 'bg-red-100';
                                    $status = 'EXPIRED';
                                } elseif ($isWarning) {
                                    $rowClass = 'bg-yellow-100';
                                    $status = 'WARNING';
                                } else {
                                    $rowClass = $index % 2 == 0 ? 'bg-white' : 'bg-gray-50';
                                }

                                // Get term expiration dates
                                $termExpirations = ['N/A', 'N/A', 'N/A', 'N/A', 'N/A'];
                                if ($termDates && $termDates->count() > 1) {
                                    for($i = 1; $i <= 5; $i++) {
                                        $term = $termDates->skip($i)->first();
                                        if ($term) {
                                            $termExpirations[$i-1] = $term['expiration_date']->format('m/d/Y');
                                        }
                                    }
                                }
                            @endphp

                            <tr class="{{ $rowClass }} hover:bg-blue-50">
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $lease->store_number ?: 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->aws ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($lease->total_rent ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right">{{ $lease->lease_to_sales_ratio ? number_format($lease->lease_to_sales_ratio * 100, 2) . '%' : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $currentTerm ? ($currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted']) : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $termExpirations[0] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $termExpirations[1] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $termExpirations[2] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $termExpirations[3] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $termExpirations[4] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $timeUntilFranchiseExpires ? ($timeUntilFranchiseExpires['expired'] ? 'Expired' : $timeUntilFranchiseExpires['formatted']) : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center font-semibold {{ $status == 'EXPIRED' ? 'text-red-600' : ($status == 'WARNING' ? 'text-yellow-600' : 'text-green-600') }}">{{ $status }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('leaseTrackerModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#3B82F6] text-white text-sm font-medium rounded hover:bg-[#2563EB]">
                    Close
                </button>
            </div>
        </div>
    </div>
