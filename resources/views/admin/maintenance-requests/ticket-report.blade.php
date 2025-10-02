<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Simple Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Ticket Report</h1>
            <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <!-- Excel-like Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse" id="ticketReportTable">
                    <thead>
                    <tr class="bg-[#ff671b] text-white">
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(0, 'text')" id="ticketreport-header-0">
                            <div class="flex items-center justify-center">
                                Entry #
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-0">A↓</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(1, 'text')" id="ticketreport-header-1">
                            <div class="flex items-center justify-between">
                                Store #
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-1">A↓</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(2, 'text')" id="ticketreport-header-2">
                            <div class="flex items-center justify-between">
                                Equipment
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-2">A↓</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(3, 'text')" id="ticketreport-header-3">
                            <div class="flex items-center justify-between">
                                Status
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-3">A↓</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(4, 'text')" id="ticketreport-header-4">
                            <div class="flex items-center justify-between">
                                Assigned to
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-4">A↓</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(5, 'date')" id="ticketreport-header-5">
                            <div class="flex items-center justify-center">
                                Due Date
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-5">↑</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(6, 'date')" id="ticketreport-header-6">
                            <div class="flex items-center justify-center">
                                Task End Date
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-6">↑</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(6, 'date')" id="ticketreport-header-6">
                            <div class="flex items-center justify-center">
                                Created Date
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-6">↑</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(7, 'text')" id="ticketreport-header-7">
                            <div class="flex items-center justify-between">
                                Urgency
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-7">A↓</span>
                            </div>
                        </th>
                        <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                            onclick="sortTicketReportTable(8, 'number')" id="ticketreport-header-8">
                            <div class="flex items-center justify-end">
                                Costs
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-8">↑</span>
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody id="ticketReportBody">
                    @foreach($maintenanceRequests as $index => $request)
                        <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-[#fff4ed]">
                            <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $request->entry_number ?? $request->id }}">{{ $request->entry_number ?? $request->id }}</td>

                            <td class="border border-gray-300 px-3 py-3 text-sm" data-sort="{{ is_object($request->store) ? $request->store->store_number : ($request->store ?? 'N/A') }}">
                                @if($request->store && is_object($request->store))
                                    {{ $request->store->store_number }} - {{ $request->store->name ?: 'No Name' }}
                                @else
                                    {{ $request->store ?: 'No Store' }}
                                @endif
                            </td>

                            <td class="border border-gray-300 px-3 py-3 text-sm" data-sort="{{ $request->equipment_with_issue ?? 'No Equipment' }}">{{ Str::limit($request->equipment_with_issue ?? 'No Equipment', 30) }}</td>

                            <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $request->status }}">
                                @switch($request->status)
                                    @case('on_hold')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            On Hold
                                        </span>
                                        @break
                                    @case('received')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Received
                                        </span>
                                        @break
                                    @case('in_progress')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            In Progress
                                        </span>
                                        @break
                                    @case('done')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Done
                                        </span>
                                        @break
                                    @case('canceled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Canceled
                                        </span>
                                        @break
                                @endswitch
                            </td>

                            <td class="border border-gray-300 px-3 py-3 text-sm" data-sort="{{ $request->effective_assigned_user ? $request->effective_assigned_user->name : 'Not Assigned' }}">
                                @if($request->effective_assigned_user)
                                    <div class="flex items-center">
                                        <div class="font-medium">{{ $request->effective_assigned_user->name }}</div>
                                        <div class="text-xs text-gray-500 ml-1">
                                            ({{ $request->assignment_source === 'task_assignment' ? 'Task' : 'Direct' }})
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500">Not Assigned</span>
                                @endif
                            </td>

                            <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $request->effective_due_date ? $request->effective_due_date->format('Y-m-d') : '9999-12-31' }}">
                                @if($request->effective_due_date)
                                    <div>
                                        <div class="font-medium">{{ $request->effective_due_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $request->effective_due_date->format('g:i A') }}</div>
                                    </div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>

                            <!-- NEW: Task End Date Column -->
                            <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $request->task_end_date ? $request->task_end_date->format('Y-m-d') : '9999-12-31' }}">
                                @if($request->task_end_date)
                                    <div class="flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-green-700">{{ $request->task_end_date->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $request->task_end_date->format('g:i A') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $request->created_at ? $request->created_at->format('Y-m-d') : '9999-12-31' }}">
                                {{ $request->created_at ? $request->created_at->format('M d, Y') : 'N/A' }}
                            </td>

                            <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $request->urgencyLevel ? $request->urgencyLevel->name : 'Unknown' }}">
                                @if($request->urgencyLevel)
                                    @switch($request->urgencyLevel->name)
                                        @case('Critical')
                                        @case('Impacts Sales')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('High')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('Medium')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('Low')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                    @endswitch
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Unknown
                                    </span>
                                @endif
                            </td>

                            <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $request->costs ?: 0 }}">
                                @if($request->costs)
                                    ${{ number_format($request->costs, 2) }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="bg-[#e55b17] text-white font-semibold">
                        <td class="border border-gray-300 px-3 py-3 text-sm text-center">TOTAL</td>
                        <td class="border border-gray-300 px-3 py-3 text-sm">{{ $maintenanceRequests->count() }} Tickets</td>
                        <td class="border border-gray-300 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-300 px-3 py-3 text-sm text-center">
                            @php
                                $statusCounts = $maintenanceRequests->groupBy('status')->map->count();
                            @endphp
                            Done: {{ $statusCounts['done'] ?? 0 }} |
                            In Progress: {{ $statusCounts['in_progress'] ?? 0 }}
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-sm">
                            {{-- ✅ Show assignment summary --}}
                            @php
                                $assignedCount = $maintenanceRequests->filter(fn($r) => $r->effective_assigned_user)->count();
                            @endphp
                            Assigned: {{ $assignedCount }} / {{ $maintenanceRequests->count() }}
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-300 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-300 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-300 px-3 py-3 text-sm text-right">
                            ${{ number_format($maintenanceRequests->sum('costs'), 2) }}
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
