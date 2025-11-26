@extends('layouts.app')

@section('title', 'Native Maintenance Requests - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Native Maintenance Requests</h1>
            <p class="mt-2 text-sm text-gray-600">Manage all native maintenance requests from store managers</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openReportModal()" 
                    class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-medium rounded-lg shadow-lg transition-all hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Generate Report
            </button>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gray-600 rounded-lg p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['all'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-sm border border-yellow-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-700">Pending</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ $statusCounts['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-lg p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-700">In Progress</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $statusCounts['in_progress'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-700">Done</p>
                    <p class="text-2xl font-bold text-green-900">{{ $statusCounts['done'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-sm border border-red-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-lg p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-700">Canceled</p>
                    <p class="text-2xl font-bold text-red-900">{{ $statusCounts['canceled'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6 border border-gray-200">
        <form method="GET" action="{{ route('admin.native.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        <option value="all">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                        <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urgency</label>
                    <select name="urgency" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        <option value="all">All Urgencies</option>
                        @foreach($urgencyLevels as $level)
                            <option value="{{ $level->id }}" {{ request('urgency') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store</label>
                    <select name="store" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        <option value="all">All Stores</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ request('store') == $store->id ? 'selected' : '' }}>{{ $store->store_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                    <select name="assigned_to" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        <option value="all">All</option>
                        <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search equipment, description, store..."
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.native.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Requests Table --}}
    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
        @if($maintenanceRequests->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Store</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Submitted By</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Equipment</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Urgency</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Assigned</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($maintenanceRequests as $index => $req)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $req->id }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $req->store->store_number ?? '' }}
                                    <div class="text-xs text-gray-500">{{ $req->store->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $req->display_requester_name }}
                                    @if($req->is_from_cognito)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 ml-1">
                                            Cognito
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <div class="font-medium text-gray-900">{{ $req->equipment_with_issue }}</div>
                                    <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $req->description_of_issue }}">
                                        {{ Str::limit($req->description_of_issue, 50) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($req->urgencyLevel)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $req->urgencyLevel->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'received' => 'bg-purple-100 text-purple-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'done' => 'bg-green-100 text-green-800',
                                            'canceled' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$req->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucwords(str_replace('_', ' ', $req->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $req->assignedTo?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $req->request_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('admin.native.show', $req) }}" class="text-orange-600 hover:text-orange-900 font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $maintenanceRequests->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No requests found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters.</p>
            </div>
        @endif
    </div>
</div>

{{-- Report Modal --}}
<div id="reportModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-7xl shadow-2xl rounded-lg bg-white">
        {{-- Modal Header --}}
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Native Maintenance Requests Report</h3>
                <p class="text-sm text-gray-500 mt-1">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>
            <div class="flex gap-2 items-center">
                <select id="reportLanguage" onchange="switchLanguage()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="en">English</option>
                    <option value="ar">العربية</option>
                </select>
                <button onclick="captureScreenshot()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Screenshot
                </button>
                <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Modal Body --}}
        <div id="reportContent" class="mt-4 max-h-[70vh] overflow-auto">
            <div class="text-center py-12">
                <svg class="animate-spin h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-4 text-gray-500">Loading report...</p>
            </div>
        </div>
    </div>
</div>

<script>
function openReportModal() {
    // Get current filter values from the page
    const statusSelect = document.querySelector('select[name="status"]');
    const urgencySelect = document.querySelector('select[name="urgency"]');
    const storeSelect = document.querySelector('select[name="store"]');
    const assignedToSelect = document.querySelector('select[name="assigned_to"]');
    const searchInput = document.querySelector('input[name="search"]');
    
    const filters = {
        status: statusSelect ? statusSelect.value : 'all',
        urgency: urgencySelect ? urgencySelect.value : 'all',
        store: storeSelect ? storeSelect.value : 'all',
        assigned_to: assignedToSelect ? assignedToSelect.value : 'all',
        search: searchInput ? searchInput.value : ''
    };
    
    // Show modal
    document.getElementById('reportModal').classList.remove('hidden');
    
    // Load report via AJAX
    const params = new URLSearchParams(filters);
    fetch(`{{ route('admin.native.ticketReport') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(html => {
        document.getElementById('reportContent').innerHTML = html;
        // Apply current language after loading
        const currentLang = document.getElementById('reportLanguage').value;
        if (currentLang === 'ar') {
            switchLanguage();
        }
    })
    .catch(error => {
        console.error('Error loading report:', error);
        document.getElementById('reportContent').innerHTML = 
            '<div class="text-center py-12"><p class="text-red-500">Failed to load report. Please try again.</p></div>';
    });
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    // Reset to English
    document.getElementById('reportLanguage').value = 'en';
    switchLanguage();
}

function switchLanguage() {
    const language = document.getElementById('reportLanguage').value;
    const wrapper = document.getElementById('report-table-wrapper');
    const table = document.getElementById('mainReportTable');
    
    if (!wrapper || !table) return;
    
    if (language === 'ar') {
        // Apply RTL layout
        wrapper.classList.remove('ltr-layout');
        wrapper.classList.add('rtl-layout');
        
        // Translate headers
        const headers = table.querySelectorAll('thead th');
        headers.forEach(th => {
            const arText = th.getAttribute('data-ar');
            if (arText) th.textContent = arText;
        });
        
        // Reverse column order for Arabic
        const headerRow = document.getElementById('headerRow');
        if (headerRow) {
            const headerCells = Array.from(headerRow.children);
            headerCells.reverse().forEach(cell => headerRow.appendChild(cell));
        }
        
        // Reverse all body rows
        const bodyRows = table.querySelectorAll('tbody tr');
        bodyRows.forEach(row => {
            const cells = Array.from(row.children);
            cells.reverse().forEach(cell => row.appendChild(cell));
        });
        
        // Translate status and urgency
        table.querySelectorAll('[data-status-ar]').forEach(cell => {
            cell.textContent = cell.getAttribute('data-status-ar');
        });
        
        table.querySelectorAll('[data-urgency-ar]').forEach(cell => {
            cell.textContent = cell.getAttribute('data-urgency-ar');
        });
        
        // Translate empty state
        table.querySelectorAll('[data-ar]').forEach(el => {
            const arText = el.getAttribute('data-ar');
            if (arText && el.tagName !== 'TH') el.textContent = arText;
        });
        
    } else {
        // Apply LTR layout
        wrapper.classList.remove('rtl-layout');
        wrapper.classList.add('ltr-layout');
        
        // Restore English headers
        const headers = table.querySelectorAll('thead th');
        headers.forEach(th => {
            const enText = th.getAttribute('data-en');
            if (enText) th.textContent = enText;
        });
        
        // Restore original column order
        const headerRow = document.getElementById('headerRow');
        if (headerRow) {
            const headerCells = Array.from(headerRow.children);
            headerCells.reverse().forEach(cell => headerRow.appendChild(cell));
        }
        
        // Restore all body rows
        const bodyRows = table.querySelectorAll('tbody tr');
        bodyRows.forEach(row => {
            const cells = Array.from(row.children);
            cells.reverse().forEach(cell => row.appendChild(cell));
        });
        
        // Restore English status and urgency
        table.querySelectorAll('[data-status]').forEach(cell => {
            cell.textContent = cell.getAttribute('data-status');
        });
        
        table.querySelectorAll('[data-urgency]').forEach(cell => {
            cell.textContent = cell.getAttribute('data-urgency');
        });
        
        // Restore English empty state
        table.querySelectorAll('[data-en]').forEach(el => {
            const enText = el.getAttribute('data-en');
            if (enText && el.tagName !== 'TH') el.textContent = enText;
        });
    }
}

function captureScreenshot() {
    const reportContent = document.getElementById('reportContent');
    const reportContainer = document.getElementById('excel-report-container');
    const language = document.getElementById('reportLanguage').value;
    const modalBody = reportContent.parentElement;
    
    if (!reportContainer) {
        alert('Report not loaded yet. Please wait for the report to finish loading.');
        return;
    }
    
    // Save original styles
    const originalMaxHeight = modalBody.style.maxHeight;
    const originalOverflow = modalBody.style.overflow;
    const originalHeight = reportContainer.style.height;
    
    // Force full height - remove scrolling
    modalBody.style.maxHeight = 'none';
    modalBody.style.overflow = 'visible';
    reportContainer.style.height = 'auto';
    
    // Wait for layout to settle
    setTimeout(() => {
        // Use html2canvas for better rendering
        if (typeof html2canvas !== 'undefined') {
            html2canvas(reportContainer, {
                scale: 2,
                useCORS: true,
                allowTaint: false,
                backgroundColor: '#ffffff',
                logging: false,
                windowWidth: reportContainer.scrollWidth,
                windowHeight: reportContainer.scrollHeight,
                width: reportContainer.scrollWidth,
                height: reportContainer.scrollHeight
            }).then(canvas => {
                // Restore original styles
                modalBody.style.maxHeight = originalMaxHeight;
                modalBody.style.overflow = originalOverflow;
                reportContainer.style.height = originalHeight;
                
                // Download
                canvas.toBlob(blob => {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.download = 'native-requests-report-' + new Date().toISOString().split('T')[0] + '-' + language + '.png';
                    link.href = url;
                    link.click();
                    URL.revokeObjectURL(url);
                });
            }).catch(error => {
                // Restore on error
                modalBody.style.maxHeight = originalMaxHeight;
                modalBody.style.overflow = originalOverflow;
                reportContainer.style.height = originalHeight;
                
                console.error('Screenshot failed:', error);
                alert('Failed to capture screenshot. Please try using your browser print function (Ctrl+P).');
            });
        } else {
            alert('Screenshot library not loaded. Please refresh the page and try again.');
            // Restore styles
            modalBody.style.maxHeight = originalMaxHeight;
            modalBody.style.overflow = originalOverflow;
            reportContainer.style.height = originalHeight;
        }
    }, 500);
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('reportModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeReportModal();
            }
        });
    }
});
</script>

<!-- Screenshot Library - Using html2canvas for better support -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

@endsection
