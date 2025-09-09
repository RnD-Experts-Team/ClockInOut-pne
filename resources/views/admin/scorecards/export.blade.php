<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Scorecards Export</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#ff671b',
                        'primary-dark': '#c74f14',
                    },
                },
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
        }
        /* Print styles */
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        /* Custom styles for better screenshot quality */
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #d1d5db;
        }
    </style>
</head>
<body class="bg-white">
<div class="py-8">
    <div class="max-w-full mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white bg-[#ff671b] py-4 px-8 rounded-lg inline-block mb-4">
                Week 9 Score Card
            </h1>
            <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <!-- Scorecards Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                    <tr class="bg-[#ff671b] text-white">
                        <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold">Name</th>
                        <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold">Hourly Rate</th>
                        <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold">Total Hours</th>
                        <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold">Total Hourly Pay</th>
                        <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold">Payments Made to the Company</th>
                        <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold">Fuel Cost</th>
                        <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold">Additional Payment</th>
                        <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($scorecards as $index => $scorecard)
                        <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="border border-gray-300 px-4 py-3 text-sm">{{ $scorecard->user->name }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">${{ number_format($scorecard->hourly_rate, 2) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">{{ $scorecard->total_hours }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">${{ number_format($scorecard->total_hourly_pay, 2) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">${{ number_format($scorecard->payments_to_company, 2) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">${{ number_format($scorecard->fuel_cost, 2) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">$0.00</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm font-bold bg-yellow-50">${{ number_format($scorecard->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">No Scorecard Data Found</h3>
                                    <p class="text-sm text-gray-500">No employee scorecard data available for the selected period.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>

                    @if($scorecards->count() > 0)
                        <!-- Totals Row -->
                        <tfoot>
                        <tr class="bg-[#e55b17] text-white font-semibold">
                            <td class="border border-gray-300 px-4 py-3 text-sm text-center">TOTAL</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">-</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">{{ number_format($scorecards->sum('total_hours'), 2) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">${{ number_format($scorecards->sum('total_hourly_pay'), 2) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">${{ number_format($scorecards->sum('payments_to_company'), 2) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">${{ number_format($scorecards->sum('fuel_cost'), 2) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm">$0.00</td>
                            <td class="border border-gray-300 px-4 py-3 text-center text-sm font-bold">${{ number_format($scorecards->sum('total'), 2) }}</td>
                        </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
