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
                <table class="min-w-full border-collapse excel-table" id="ticketReportTable">
                    <thead>
                    <tr class="bg-[#2d3748] text-white table-header">
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(0, 'text')" id="ticketreport-header-0" style="min-width: 100px;">
                            <div class="flex items-center justify-center">
                                Entry #
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-0">A↓</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(1, 'text')" id="ticketreport-header-1" style="min-width: 150px;">
                            <div class="flex items-center justify-between">
                                Store #
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-1">A↓</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(2, 'text')" id="ticketreport-header-2" style="min-width: 200px;">
                            <div class="flex items-center justify-between">
                                Equipment
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-2">A↓</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(3, 'text')" id="ticketreport-header-3" style="min-width: 120px;">
                            <div class="flex items-center justify-between">
                                Status
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-3">A↓</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(4, 'text')" id="ticketreport-header-4" style="min-width: 150px;">
                            <div class="flex items-center justify-between">
                                Assigned to
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-4">A↓</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(5, 'date')" id="ticketreport-header-5" style="min-width: 130px;">
                            <div class="flex items-center justify-center">
                                Due Date
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-5">↑</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(6, 'date')" id="ticketreport-header-6" style="min-width: 130px;">
                            <div class="flex items-center justify-center">
                                Task End Date
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-6">↑</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(7, 'date')" id="ticketreport-header-7" style="min-width: 130px;">
                            <div class="flex items-center justify-center">
                                Created Date
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-7">↑</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(8, 'text')" id="ticketreport-header-8" style="min-width: 120px;">
                            <div class="flex items-center justify-between">
                                Urgency
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-8">A↓</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                        <th class="border border-gray-400 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#1a202c] transition-colors select-none resizable-column"
                            onclick="sortTicketReportTable(9, 'number')" id="ticketreport-header-9" style="min-width: 100px;">
                            <div class="flex items-center justify-end">
                                Costs
                                <span class="ml-2 text-xs opacity-75" id="ticketreport-sort-indicator-9">↑</span>
                            </div>
                            <div class="resize-handle"></div>
                        </th>
                    </tr>
                    </thead>
                    <tbody id="ticketReportBody">
                    @foreach($maintenanceRequests as $index => $request)
                        @php
                            $statusClass = 'status-' . ($request->status ?? 'unknown') . '-cell';
                            $urgencyClass = 'urgency-' . strtolower(str_replace([' ', '_'], '-', $request->urgencyLevel->name ?? 'unknown')) . '-cell';
                            $rowClass = $index % 2 == 0 ? 'table-row-even' : 'table-row-odd';
                            $costsClass = $request->costs ? 'costs-cell' : 'costs-na-cell';
                        @endphp
                        <tr class="{{ $rowClass }} table-row-hover">
                            <td class="border border-gray-400 px-3 py-3 text-sm text-center excel-cell" data-sort="{{ $request->entry_number ?? $request->id }}">
                                {{ $request->entry_number ?? $request->id }}
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm excel-cell" data-sort="{{ is_object($request->store) ? $request->store->store_number : ($request->store ?? 'N/A') }}">
                                @if($request->store && is_object($request->store))
                                    {{ $request->store->store_number }} - {{ $request->store->name ?: 'No Name' }}
                                @else
                                    {{ $request->store ?: 'No Store' }}
                                @endif
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm excel-cell" data-sort="{{ $request->equipment_with_issue ?? 'No Equipment' }}">
                                {{ Str::limit($request->equipment_with_issue ?? 'No Equipment', 30) }}
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm text-center excel-cell {{ $statusClass }}" data-sort="{{ $request->status }}">
                                @switch($request->status)
                                    @case('on_hold')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-200 text-orange-900 border border-orange-300">
                                            On Hold
                                        </span>
                                        @break
                                    @case('received')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-200 text-purple-900 border border-purple-300">
                                            Received
                                        </span>
                                        @break
                                    @case('in_progress')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-200 text-blue-900 border border-blue-300">
                                            In Progress
                                        </span>
                                        @break
                                    @case('done')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-200 text-green-900 border border-green-300">
                                            Done
                                        </span>
                                        @break
                                    @case('canceled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-200 text-red-900 border border-red-300">
                                            Canceled
                                        </span>
                                        @break
                                @endswitch
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm excel-cell" data-sort="{{ $request->effective_assigned_user ? $request->effective_assigned_user->name : 'Not Assigned' }}">
                                @if($request->effective_assigned_user)
                                    <div class="flex items-center">
                                        <div class="font-medium">{{ $request->effective_assigned_user->name }}</div>
                                        <div class="text-xs text-gray-600 ml-1">
                                            ({{ $request->assignment_source === 'task_assignment' ? 'Task' : 'Direct' }})
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-600">Not Assigned</span>
                                @endif
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm text-center excel-cell" data-sort="{{ $request->effective_due_date ? $request->effective_due_date->format('Y-m-d') : '9999-12-31' }}">
                                @if($request->effective_due_date)
                                    <div>
                                        <div class="font-medium">{{ $request->effective_due_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-600">{{ $request->effective_due_date->format('g:i A') }}</div>
                                    </div>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm text-center excel-cell task-completed-cell" data-sort="{{ $request->task_end_date ? $request->task_end_date->format('Y-m-d') : '9999-12-31' }}">
                                @if($request->task_end_date)
                                    <div class="flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-green-800">{{ $request->task_end_date->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-600">{{ $request->task_end_date->format('g:i A') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm text-center excel-cell" data-sort="{{ $request->created_at ? $request->created_at->format('Y-m-d') : '9999-12-31' }}">
                                {{ $request->created_at ? $request->created_at->format('M d, Y') : 'N/A' }}
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm text-center excel-cell {{ $urgencyClass }}" data-sort="{{ $request->urgencyLevel ? $request->urgencyLevel->name : 'Unknown' }}">
                                @if($request->urgencyLevel)
                                    @switch($request->urgencyLevel->name)
                                        @case('Critical')
                                        @case('Impacts Sales')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-200 text-red-900 border border-red-300">
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('High')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-200 text-orange-900 border border-orange-300">
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('Medium')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-200 text-yellow-900 border border-yellow-300">
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('Low')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-200 text-green-900 border border-green-300">
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                    @endswitch
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-900 border border-gray-300">
                                        Unknown
                                    </span>
                                @endif
                            </td>

                            <td class="border border-gray-400 px-3 py-3 text-sm text-right excel-cell {{ $costsClass }}" data-sort="{{ $request->costs ?: 0 }}">
                                @if($request->costs)
                                    ${{ number_format($request->costs, 2) }}
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="table-footer">
                        <td class="border border-gray-400 px-3 py-3 text-sm text-center font-bold">TOTAL</td>
                        <td class="border border-gray-400 px-3 py-3 text-sm font-bold">{{ $maintenanceRequests->count() }} Tickets</td>
                        <td class="border border-gray-400 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-400 px-3 py-3 text-sm text-center font-bold">
                            @php
                                $statusCounts = $maintenanceRequests->groupBy('status')->map->count();
                            @endphp
                            Done: {{ $statusCounts['done'] ?? 0 }} |
                            In Progress: {{ $statusCounts['in_progress'] ?? 0 }}
                        </td>
                        <td class="border border-gray-400 px-3 py-3 text-sm font-bold">
                            @php
                                $assignedCount = $maintenanceRequests->filter(fn($r) => $r->effective_assigned_user)->count();
                            @endphp
                            Assigned: {{ $assignedCount }} / {{ $maintenanceRequests->count() }}
                        </td>
                        <td class="border border-gray-400 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-400 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-400 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-400 px-3 py-3 text-sm"></td>
                        <td class="border border-gray-400 px-3 py-3 text-sm text-right font-bold">
                            ${{ number_format($maintenanceRequests->sum('costs'), 2) }}
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

    .rtl {
        direction: rtl;
        font-family: 'Tajawal', sans-serif;
    }

    .ltr {
        direction: ltr;
    }

    /* Excel-like Table Styling */
    .excel-table {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 12px;
        table-layout: fixed;
    }

    .excel-cell {
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        padding: 8px 12px;
        vertical-align: middle;
        word-wrap: break-word;
        overflow: hidden;
    }

    /* Resizable Columns */
    .resizable-column {
        position: relative;
        resize: horizontal;
        overflow: hidden;
    }

    .resize-handle {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 5px;
        cursor: col-resize;
        background: transparent;
        border-right: 1px solid #4a5568;
    }

    .resize-handle:hover {
        background: #718096;
    }

    /* Darker Status Cell Styles */
    .status-on_hold-cell {
        background: linear-gradient(135deg, #f6ad55, #ed8936) !important;
        border-left: 4px solid #c05621;
        color: #744210 !important;
    }
    .status-received-cell {
        background: linear-gradient(135deg, #b794f6, #9f7aea) !important;
        border-left: 4px solid #553c9a;
        color: #44337a !important;
    }
    .status-in_progress-cell {
        background: linear-gradient(135deg, #63b3ed, #4299e1) !important;
        border-left: 4px solid #2b6cb0;
        color: #2c5282 !important;
    }
    .status-done-cell {
        background: linear-gradient(135deg, #68d391, #48bb78) !important;
        border-left: 4px solid #2f855a;
        color: #276749 !important;
    }
    .status-canceled-cell {
        background: linear-gradient(135deg, #fc8181, #f56565) !important;
        border-left: 4px solid #c53030;
        color: #742a2a !important;
    }

    /* Darker Urgency Cell Styles */
    .urgency-critical-cell,
    .urgency-impacts-sales-cell {
        background: linear-gradient(135deg, #fc8181, #f56565) !important;
        border-left: 4px solid #c53030;
        color: #742a2a !important;
    }
    .urgency-high-cell {
        background: linear-gradient(135deg, #f6ad55, #ed8936) !important;
        border-left: 4px solid #c05621;
        color: #744210 !important;
    }
    .urgency-medium-cell {
        background: linear-gradient(135deg, #f6e05e, #ecc94b) !important;
        border-left: 4px solid #b7791f;
        color: #744210 !important;
    }
    .urgency-low-cell {
        background: linear-gradient(135deg, #68d391, #48bb78) !important;
        border-left: 4px solid #2f855a;
        color: #276749 !important;
    }
    .urgency-unknown-cell {
        background: linear-gradient(135deg, #a0aec0, #718096) !important;
        border-left: 4px solid #4a5568;
        color: #ff671b !important;
    }

    /* Darker Task and Cost Cell Styles */
    .task-completed-cell {
        background: linear-gradient(135deg, #68d391, #48bb78) !important;
        border-left: 4px solid #2f855a;
        color: #276749 !important;
    }
    .costs-cell {
        background: linear-gradient(135deg, #68d391, #48bb78) !important;
        border-left: 4px solid #2f855a;
        color: #276749 !important;
    }
    .costs-na-cell {
        background: linear-gradient(135deg, #a0aec0, #718096) !important;
        border-left: 4px solid #4a5568;
        color: #2d3748 !important;
    }

    /* Table Header and Row Styles */
    .table-header {
        background: linear-gradient(135deg, rgba(188, 104, 21, 0.5), #ff671b) !important;
        color: white !important;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #4a5568;
    }
    .table-header:hover {
        background: linear-gradient(135deg, rgba(188, 104, 21, 0.5), #ff671b) !important;
    }
    .table-row-even {
        background-color: #f7fafc;
    }
    .table-row-odd {
        background-color: #edf2f7;
    }
    .table-row-hover:hover td {
        background-color: #e2e8f0 !important;
        transform: none;
        transition: background-color 0.2s ease;
    }
    .table-footer {
        background: linear-gradient(135deg, #2d3748, #1a202c) !important;
        color: white !important;
        font-weight: 700;
        border: 1px solid #4a5568;
    }

    /* Excel-like borders */
    .excel-table th,
    .excel-table td {
        border: 1px solid #9ca3af;
        border-collapse: collapse;
    }

    /* Column resize functionality */
    .excel-table th {
        user-select: none;
    }

    /* Status badges with darker colors */
    .excel-table .bg-orange-200 { background-color: #fed7aa !important; color: #9a3412 !important; }
    .excel-table .bg-purple-200 { background-color: #e9d5ff !important; color: #581c87 !important; }
    .excel-table .bg-blue-200 { background-color: #bfdbfe !important; color: #1e3a8a !important; }
    .excel-table .bg-green-200 { background-color: #bbf7d0 !important; color: #14532d !important; }
    .excel-table .bg-red-200 { background-color: #fecaca !important; color: #7f1d1d !important; }
    .excel-table .bg-yellow-200 { background-color: #fef08a !important; color: #713f12 !important; }
    .excel-table .bg-gray-200 { background-color: #e5e7eb !important; color: #1f2937 !important; }

</style>

<script>
    let isResizing = false;
    let currentColumn = null;
    let startX = 0;
    let startWidth = 0;

    // Initialize column resizing
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('ticketReportTable');
        const resizeHandles = table.querySelectorAll('.resize-handle');

        resizeHandles.forEach((handle, index) => {
            handle.addEventListener('mousedown', function(e) {
                isResizing = true;
                currentColumn = handle.parentElement;
                startX = e.clientX;
                startWidth = parseInt(window.getComputedStyle(currentColumn).width, 10);

                document.addEventListener('mousemove', doResize);
                document.addEventListener('mouseup', stopResize);
                e.preventDefault();
            });
        });
    });

    function doResize(e) {
        if (!isResizing) return;

        const width = startWidth + e.clientX - startX;
        if (width > 50) { // Minimum width
            currentColumn.style.width = width + 'px';

            // Update all cells in this column
            const columnIndex = Array.from(currentColumn.parentNode.children).indexOf(currentColumn);
            const table = document.getElementById('ticketReportTable');
            const rows = table.querySelectorAll('tbody tr, tfoot tr');

            rows.forEach(row => {
                if (row.cells[columnIndex]) {
                    row.cells[columnIndex].style.width = width + 'px';
                }
            });
        }
    }

    function stopResize() {
        isResizing = false;
        currentColumn = null;
        document.removeEventListener('mousemove', doResize);
        document.removeEventListener('mouseup', stopResize);
    }

    function sortTicketReportTable(columnIndex, type) {
        const table = document.getElementById('ticketReportTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Toggle sort direction
        const header = document.getElementById(`ticketreport-header-${columnIndex}`);
        const indicator = document.getElementById(`ticketreport-sort-indicator-${columnIndex}`);
        const isAscending = indicator.textContent.includes('↓') || indicator.textContent.includes('A↓');

        // Clear all other indicators
        document.querySelectorAll('[id^="ticketreport-sort-indicator-"]').forEach(ind => {
            if (ind.id !== `ticketreport-sort-indicator-${columnIndex}`) {
                ind.textContent = type === 'date' || type === 'number' ? '↑' : 'A↓';
            }
        });

        // Sort rows
        rows.sort((a, b) => {
            const cellA = a.cells[columnIndex];
            const cellB = b.cells[columnIndex];

            let valueA, valueB;

            if (type === 'date') {
                valueA = cellA.getAttribute('data-sort') || '9999-12-31';
                valueB = cellB.getAttribute('data-sort') || '9999-12-31';
            } else if (type === 'number') {
                valueA = parseFloat(cellA.getAttribute('data-sort') || '0');
                valueB = parseFloat(cellB.getAttribute('data-sort') || '0');
            } else {
                valueA = (cellA.getAttribute('data-sort') || cellA.textContent).toLowerCase().trim();
                valueB = (cellB.getAttribute('data-sort') || cellB.textContent).toLowerCase().trim();
            }

            if (type === 'number') {
                return isAscending ? valueA - valueB : valueB - valueA;
            } else {
                if (valueA < valueB) return isAscending ? -1 : 1;
                if (valueA > valueB) return isAscending ? 1 : -1;
                return 0;
            }
        });

        // Update indicator


        // Reappend sorted rows
        rows.forEach((row, index) => {
            // Update alternating row colors
            row.className = row.className.replace(/table-row-(even|odd)/, '');
            row.classList.add(index % 2 === 0 ? 'table-row-even' : 'table-row-odd');
            tbody.appendChild(row);
        });
    }
</script>
