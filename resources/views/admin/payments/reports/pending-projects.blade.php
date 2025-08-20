@extends('layouts.app')

@section('title', 'Pending Projects Report')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white bg-[#ff671b] py-4 px-8 rounded-lg inline-block mb-4">Pending Projects Report</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            @php
                // Get unpaid payments as pending projects
                $pendingProjects = \App\Models\Payment::where('paid', false)
                    ->with('company')
                    ->orderBy('date', 'asc')
                    ->get()
                    ->map(function($payment) {
                        // Determine priority based on date and cost
                        $daysSinceDate = now()->diffInDays($payment->date);
                        $priority = 'medium';

                        if ($payment->cost >= 5000) {
                            $priority = 'critical';
                        } elseif ($payment->cost >= 2000) {
                            $priority = 'high';
                        } elseif ($daysSinceDate > 90) {
                            $priority = 'urgent';
                        } elseif ($daysSinceDate > 30) {
                            $priority = 'high';
                        }

                        // Determine due date text
                        $dueDate = 'Pending';
                        if ($daysSinceDate > 90) {
                            $dueDate = 'Overdue (' . $daysSinceDate . ' days)';
                        } elseif ($daysSinceDate > 30) {
                            $dueDate = 'Due soon (' . $daysSinceDate . ' days)';
                        } elseif ($daysSinceDate > 7) {
                            $dueDate = 'This month';
                        } else {
                            $dueDate = 'This week';
                        }

                        // Handle store display
                        $storeDisplay = $payment->store ?: ($payment->store_id ? 'Store ' . $payment->store_id : 'Unknown Store');

                        return [
                            'store' => $storeDisplay,
                            'equipment' => $payment->what_got_fixed ?: $payment->maintenance_type ?: 'General Maintenance',
                            'details' => $payment->company->name ?? 'Unknown Company',
                            'due_date' => $dueDate,
                            'estimated_cost' => $payment->cost,
                            'priority' => $priority,
                            'notes' => $payment->notes,
                            'date' => $payment->date,
                            'days_old' => $daysSinceDate
                        ];
                    });

                // Add recurring maintenance needs based on payment history
                $recurringNeeds = \App\Models\Payment::select('store', 'maintenance_type', 'what_got_fixed')
                    ->selectRaw('AVG(cost) as avg_cost, COUNT(*) as frequency, MAX(date) as last_service')
                    ->where('paid', true)
                    ->whereNotNull('maintenance_type')
                    ->groupBy('store', 'maintenance_type', 'what_got_fixed')
                    ->having('frequency', '>=', 2)
                    ->get()
                    ->filter(function($item) {
                        // Only include items that haven't been serviced in the last 6 months
                        return $item->last_service && now()->diffInMonths($item->last_service) >= 6;
                    })
                    ->map(function($item) {
                        $monthsSinceService = now()->diffInMonths($item->last_service);

                        return [
                            'store' => $item->store ?: 'Unknown Store',
                            'equipment' => $item->what_got_fixed ?: $item->maintenance_type,
                            'details' => 'Recurring maintenance due',
                            'due_date' => $monthsSinceService >= 12 ? 'Overdue maintenance' : 'Maintenance due',
                            'estimated_cost' => $item->avg_cost,
                            'priority' => $monthsSinceService >= 12 ? 'urgent' : 'medium',
                            'notes' => "Last serviced " . $item->last_service->format('M Y'),
                            'date' => $item->last_service,
                            'days_old' => now()->diffInDays($item->last_service)
                        ];
                    });

                // Merge and sort all pending projects
                $allPendingProjects = $pendingProjects->concat($recurringNeeds)
                    ->sortBy(function($project) {
                        // Sort by priority and days old
                        $priorityOrder = ['critical' => 1, 'urgent' => 2, 'high' => 3, 'medium' => 4, 'low' => 5];
                        return $priorityOrder[$project['priority']] * 1000 + $project['days_old'];
                    });

                $totalCost = $allPendingProjects->sum('estimated_cost');
            @endphp

                <!-- Summary Statistics -->
            @if($allPendingProjects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $allPendingProjects->where('priority', 'critical')->count() }}</div>
                        <div class="text-sm text-gray-600">Critical Projects</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $allPendingProjects->where('priority', 'urgent')->count() }}</div>
                        <div class="text-sm text-gray-600">Urgent Projects</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-2xl font-bold text-[#ff671b]">${{ number_format($totalCost, 0) }}</div>
                        <div class="text-sm text-gray-600">Total Estimated Cost</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-2xl font-bold text-[#ff671b]">{{ $allPendingProjects->count() }}</div>
                        <div class="text-sm text-gray-600">Total Projects</div>
                    </div>
                </div>
            @endif

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                @if($allPendingProjects->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse" id="pendingProjectsTable">
                            <thead>
                            <tr class="bg-[#ff671b] text-white">
                                <th class="border border-gray-300 px-6 py-4 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortTable(0, 'text')" id="header-0">
                                    <div class="flex items-center justify-between">
                                        Store
                                        <span class="ml-2 text-xs opacity-75" id="sort-indicator-0">A↓</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-6 py-4 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortTable(1, 'text')" id="header-1">
                                    <div class="flex items-center justify-between">
                                        Equipment
                                        <span class="ml-2 text-xs opacity-75" id="sort-indicator-1">A↓</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-6 py-4 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortTable(2, 'text')" id="header-2">
                                    <div class="flex items-center justify-between">
                                        Company
                                        <span class="ml-2 text-xs opacity-75" id="sort-indicator-2">A↓</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-6 py-4 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortTable(3, 'text')" id="header-3">
                                    <div class="flex items-center justify-between">
                                        Status & Due Date
                                        <span class="ml-2 text-xs opacity-75" id="sort-indicator-3">A↓</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-6 py-4 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortTable(4, 'number')" id="header-4">
                                    <div class="flex items-center justify-end">
                                        Estimated Cost
                                        <span class="ml-2 text-xs opacity-75" id="sort-indicator-4">↑</span>
                                    </div>
                                </th>
                            </tr>
                            </thead>
                            <tbody id="tableBody">
                            @forelse($allPendingProjects as $index => $project)
                                <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-[#fff4ed]" data-row-index="{{ $index }}">
                                    <td class="border border-gray-300 px-6 py-3 text-sm text-gray-600" data-sort="{{ $project['store'] }}">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-[#ff671b] rounded-full mr-3"></div>
                                            <span class="font-medium">{{ $project['store'] }}</span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-900" data-sort="{{ $project['equipment'] }}">
                                        {{ $project['equipment'] }}
                                    </td>
                                    <td class="border border-gray-300 px-6 py-3 text-sm text-gray-600" data-sort="{{ $project['details'] }}">
                                        {{ $project['details'] }}
                                        @if(!empty($project['notes']))
                                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($project['notes'], 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 px-6 py-3 text-sm text-gray-600" data-sort="{{ $project['due_date'] }}">
                                        <div class="flex flex-col">
                                            <div class="flex items-center mb-1">
                                                @if($project['priority'] === 'critical')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                    🔴 Critical
                                                </span>
                                                @elseif($project['priority'] === 'urgent')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                                    🟠 Urgent
                                                </span>
                                                @elseif($project['priority'] === 'high')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                                    🟡 High
                                                </span>
                                                @elseif($project['priority'] === 'medium')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-[#fff4ed] text-[#ff671b] border border-[#ff671b]">
                                                    🔵 Medium
                                                </span>
                                                @endif
                                            </div>
                                            <span class="text-sm">{{ $project['due_date'] }}</span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-300 px-6 py-3 text-sm text-right" data-sort="{{ $project['estimated_cost'] }}">
                                        <div class="flex items-center justify-end">
                                            <div class="w-4 h-4 mr-2 rounded
                                            @if($project['estimated_cost'] >= 5000) bg-red-500
                                            @elseif($project['estimated_cost'] >= 1000) bg-orange-500
                                            @else bg-[#ff671b] @endif">
                                            </div>
                                            <span class="font-semibold text-lg
                                            @if($project['estimated_cost'] >= 5000) text-red-600
                                            @elseif($project['estimated_cost'] >= 1000) text-orange-600
                                            @else text-[#ff671b] @endif">
                                            ${{ number_format($project['estimated_cost'], 2) }}
                                        </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-row-type="empty">
                                    <td colspan="5" class="border border-gray-300 px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">No Pending Projects</h3>
                                            <p class="text-sm text-gray-500">All payments are up to date and no maintenance is due.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>

                            @if($allPendingProjects->count() > 0)
                                <!-- Total Row -->
                                <tfoot>
                                <tr class="bg-[#e55b17] text-white font-bold">
                                    <td class="border border-gray-300 px-6 py-4 text-sm" colspan="4">
                                        <div class="flex justify-between items-center">
                                            <span>TOTAL ESTIMATED COST</span>
                                            <span class="text-sm opacity-75">({{ $allPendingProjects->count() }} projects)</span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-300 px-6 py-4 text-sm text-right text-xl">
                                        ${{ number_format($totalCost, 2) }}
                                    </td>
                                </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                @endif
            </div>

            @if($allPendingProjects->count() > 0)
                <!-- Priority Breakdown -->
                <div class="mt-6 bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Priority Breakdown</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <div class="text-sm text-red-600 font-medium">Critical Projects</div>
                            <div class="text-2xl font-bold text-red-900">
                                {{ $allPendingProjects->where('priority', 'critical')->count() }}
                            </div>
                            <div class="text-xs text-red-600 mt-1">
                                ${{ number_format($allPendingProjects->where('priority', 'critical')->sum('estimated_cost'), 0) }}
                            </div>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                            <div class="text-sm text-orange-600 font-medium">Urgent Projects</div>
                            <div class="text-2xl font-bold text-orange-900">
                                {{ $allPendingProjects->where('priority', 'urgent')->count() }}
                            </div>
                            <div class="text-xs text-orange-600 mt-1">
                                ${{ number_format($allPendingProjects->where('priority', 'urgent')->sum('estimated_cost'), 0) }}
                            </div>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                            <div class="text-sm text-orange-600 font-medium">High Priority</div>
                            <div class="text-2xl font-bold text-orange-900">
                                {{ $allPendingProjects->where('priority', 'high')->count() }}
                            </div>
                            <div class="text-xs text-orange-600 mt-1">
                                ${{ number_format($allPendingProjects->where('priority', 'high')->sum('estimated_cost'), 0) }}
                            </div>
                        </div>
                        <div class="bg-[#fff4ed] p-4 rounded-lg border border-[#ff671b]">
                            <div class="text-sm text-[#ff671b] font-medium">Medium Priority</div>
                            <div class="text-2xl font-bold text-[#ff671b]">
                                {{ $allPendingProjects->where('priority', 'medium')->count() }}
                            </div>
                            <div class="text-xs text-[#ff671b] mt-1">
                                ${{ number_format($allPendingProjects->where('priority', 'medium')->sum('estimated_cost'), 0) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Breakdown by Store -->
                <div class="mt-6 bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Projects by Store</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($allPendingProjects->groupBy('store') as $store => $projects)
                            <div class="bg-gray-50 p-4 rounded-lg border hover:shadow-sm transition-shadow">
                                <div class="text-sm font-medium text-gray-900">{{ $store }}</div>
                                <div class="text-xl font-bold text-gray-700">{{ $projects->count() }} projects</div>
                                <div class="text-sm text-[#ff671b] font-medium mt-1">
                                    ${{ number_format($projects->sum('estimated_cost'), 0) }}
                                </div>
                                @if($projects->where('priority', 'critical')->count() > 0)
                                    <div class="text-xs text-red-600 mt-1">
                                        🔴 {{ $projects->where('priority', 'critical')->count() }} critical
                                    </div>
                                @endif
                                @if($projects->where('priority', 'urgent')->count() > 0)
                                    <div class="text-xs text-orange-600 mt-1">
                                        🟠 {{ $projects->where('priority', 'urgent')->count() }} urgent
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('pendingProjectsModal')"
                        class="inline-flex items-center px-6 py-3 bg-[#ff671b] text-white text-sm font-medium rounded-lg hover:bg-[#e55b17] transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let sortDirection = {};

            // Make sortTable function globally available
            window.sortTable = function(columnIndex, type) {
                const table = document.getElementById('pendingProjectsTable');
                const tbody = document.getElementById('tableBody');
                if (!table || !tbody) return;
                const rows = Array.from(tbody.querySelectorAll('tr[data-row-index]')); // Only sort data rows

                if (rows.length === 0) return; // No data to sort

                // Toggle sort direction
                const currentDirection = sortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                sortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i < 5; i++) {
                    const indicator = document.getElementById(`sort-indicator-${i}`);
                    if (indicator) {
                        indicator.textContent = i === 4 ? '↑' : 'A↓';
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`sort-indicator-${columnIndex}`);
                if (activeIndicator) {
                    if (type === 'number') {
                        activeIndicator.textContent = newDirection === 'asc' ? '↑' : '↓';
                    } else {
                        activeIndicator.textContent = newDirection === 'asc' ? 'A↓' : 'Z↑';
                    }
                    activeIndicator.style.opacity = '1';
                }

                // Sort rows
                rows.sort((a, b) => {
                    let aValue, bValue;
                    if (type === 'number') {
                        aValue = parseFloat(a.cells[columnIndex].getAttribute('data-sort')) || 0;
                        bValue = parseFloat(b.cells[columnIndex].getAttribute('data-sort')) || 0;
                    } else {
                        aValue = (a.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                        bValue = (b.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                    }
                    if (newDirection === 'asc') return aValue > bValue ? 1 : -1;
                    else return aValue < bValue ? 1 : -1;
                });

                // Clear tbody and re-append sorted rows with alternating colors
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }
                rows.forEach((row, index) => {
                    row.className = (index % 2 === 0 ? 'bg-gray-50' : 'bg-white') + ' hover:bg-[#fff4ed]';
                    tbody.appendChild(row);
                });

                // Re-append empty state row if present
                const emptyRow = document.querySelector('tr[data-row-type="empty"]');
                if (emptyRow) {
                    tbody.appendChild(emptyRow);
                }
            };
        });
    </script>
@endsection
