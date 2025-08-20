{{-- resources/views/payments/reports/store-image.blade.php --}}
<div class="bg-gray-50 py-4">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white bg-gray-800 py-4 px-8 rounded-lg inline-block">Store {{ $store }}</h1>
            <p class="text-gray-600 mt-2">Generated on {{ now()->format('F j, Y \\a\\t g:i A') }}</p>
        </div>

        <!-- Repair Cost Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
            <h2 class="text-2xl font-bold text-gray-900 py-4 text-center bg-gray-100">Repair Cost Breakdown</h2>

            <table class="min-w-full border-collapse">
                <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="border border-gray-300 px-4 py-2 text-left">Store#</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">What got Fixed</th>
                    <th class="border border-gray-300 px-4 py-2 text-right">Cost</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $gt = $grouped->sum(fn($rows) => $rows->sum('cost'));
                    $first = true;
                @endphp
                @foreach($grouped as $key => $rows)
                    <tr class="{{ $loop->even ? 'bg-blue-50' : 'bg-white' }}">
                        @if($first)
                            <td class="border border-gray-300 px-4 py-2 font-bold text-center" rowspan="{{ count($grouped) }}">
                                Store {{ $store }}
                            </td>
                            @php $first = false; @endphp
                        @endif
                        <td class="border border-gray-300 px-4 py-2">{{ $key ?: '(blank)' }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">${{ number_format($rows->sum('cost'), 2) }}</td>
                    </tr>
                @endforeach
                <tr class="bg-gray-800 text-white font-semibold">
                    <td class="border border-gray-300 px-4 py-2" colspan="2">Grand Total</td>
                    <td class="border border-gray-300 px-4 py-2 text-right">${{ number_format($gt, 2) }}</td>
                </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
