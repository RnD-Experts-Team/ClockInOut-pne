{{-- Excel-Style Report Table with Full Arabic Support --}}
<div id="excel-report-container" class="report-container">
    <style>
        /* Excel-style table */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        
        .excel-table thead {
            background-color: #000;
            color: #fff;
            font-weight: bold;
        }
        
        .excel-table th,
        .excel-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        
        .excel-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .excel-table tbody tr:hover {
            background-color: #f0f0f0;
        }
        
        /* Status colors */
        .status-pending {
            background-color: #FFFFE0 !important;
            color: #856404;
        }
        
        .status-in-progress {
            background-color: #CCE5FF !important;
            color: #004085;
        }
        
        .status-done {
            background-color: #90EE90 !important;
            color: #155724;
        }
        
        .status-canceled {
            background-color: #FFB6C1 !important;
            color: #721c24;
        }
        
        /* Arabic RTL styles */
        .rtl-layout {
            direction: rtl;
            text-align: right;
        }
        
        .rtl-layout th,
        .rtl-layout td {
            text-align: right;
        }
        
        .ltr-layout {
            direction: ltr;
            text-align: left;
        }
        
        .ltr-layout th,
        .ltr-layout td {
            text-align: left;
        }
        
        /* Print optimization */
        @media print {
            .excel-table {
                page-break-inside: avoid;
            }
        }
    </style>
    
    <div id="report-table-wrapper" class="ltr-layout">
        <table class="excel-table" id="mainReportTable">
            <thead>
                <tr id="headerRow">
                    <th data-en="ID" data-ar="معرف">ID</th>
                    <th data-en="Store" data-ar="المتجر">Store</th>
                    <th data-en="Submitted By" data-ar="مقدم الطلب">Submitted By</th>
                    <th data-en="Equipment" data-ar="المعدة">Equipment</th>
                    <th data-en="Description" data-ar="الوصف">Description</th>
                    <th data-en="Urgency" data-ar="الأولوية">Urgency</th>
                    <th data-en="Status" data-ar="الحالة">Status</th>
                    <th data-en="Assigned To" data-ar="المسؤول">Assigned To</th>
                    <th data-en="Images" data-ar="الصور">Images</th>
                    <th data-en="Costs" data-ar="التكلفة">Costs</th>
                    <th data-en="Date" data-ar="التاريخ">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenanceRequests as $req)
                <tr>
                    <td>{{ $req->id }}</td>
                    <td>
                        <strong>{{ $req->store->store_number }}</strong><br>
                        <small style="color: #666;">{{ $req->store->name }}</small>
                    </td>
                    <td>{{ $req->requester->name }}</td>
                    <td>{{ $req->equipment_with_issue }}</td>
                    <td style="max-width: 200px; white-space: normal;">{{ Str::limit($req->description_of_issue, 100) }}</td>
                    <td style="background-color: {{ $req->urgencyLevel->color }}20; color: {{ $req->urgencyLevel->color }}; font-weight: bold;"
                        data-urgency="{{ $req->urgencyLevel->name }}"
                        data-urgency-ar="{{ 
                            $req->urgencyLevel->name === 'Critical' ? 'حرج' : (
                            $req->urgencyLevel->name === 'High' ? 'عالي' : (
                            $req->urgencyLevel->name === 'Medium' ? 'متوسط' : (
                            $req->urgencyLevel->name === 'Low' ? 'منخفض' : $req->urgencyLevel->name
                        ))) }}">
                        {{ $req->urgencyLevel->name }}
                    </td>
                    <td class="status-{{ $req->status }}"
                        data-status="{{ ucwords(str_replace('_', ' ', $req->status)) }}"
                        data-status-ar="{{ 
                            $req->status === 'pending' ? 'معلق' : (
                            $req->status === 'in_progress' ? 'قيد التنفيذ' : (
                            $req->status === 'done' ? 'مكتمل' : (
                            $req->status === 'canceled' ? 'ملغي' : ucwords(str_replace('_', ' ', $req->status))
                        ))) }}">
                        {{ ucwords(str_replace('_', ' ', $req->status)) }}
                    </td>
                    <td>{{ $req->assignedTo?->name ?? '-' }}</td>
                    <td style="text-align: center;">
                        @if($req->attachments->count() > 0)
                            📷 {{ $req->attachments->count() }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $req->costs ?? '-' }}</td>
                    <td style="white-space: nowrap;">{{ $req->request_date->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: #999;">
                        <span data-en="No requests found" data-ar="لا توجد طلبات">No requests found</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
