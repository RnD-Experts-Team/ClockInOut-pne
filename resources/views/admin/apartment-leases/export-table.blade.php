<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Apartment Lease Report</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 20px;
            background: white;
            color: #1f2937;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1f2937;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .header p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }

        .table-container {
            overflow: visible;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th {
            background: #f8fafc;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #fafafa;
        }

        .status-expired {
            color: #dc2626;
            font-weight: 600;
        }

        .status-warning {
            color: #d97706;
            font-weight: 600;
        }

        .status-active {
            color: #059669;
            font-weight: 600;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<!-- Header -->
<div class="header">
    <h1>Apartment Lease Management Report</h1>
    <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
        <div class="stat-label">Total Apartments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">${{ number_format($stats['total_monthly_rent'] ?? 0) }}</div>
        <div class="stat-label">Total Monthly Rent</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['families'] ?? 0 }}</div>
        <div class="stat-label">Family Units</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_cars'] ?? 0 }}</div>
        <div class="stat-label">Total Cars</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_at'] ?? 0 }}</div>
        <div class="stat-label">Total AT</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['expiring_soon'] ?? 0 }}</div>
        <div class="stat-label">Expiring Soon</div>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table>
        <thead>
        <tr>
            <th>Store #</th>
            <th>Address</th>
            <th>Monthly Rent</th>
            <th>AT</th>
            <th>Cars</th>
            <th>Family</th>
            <th>Lease Holder</th>
            <th>Expiration</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($leases as $lease)
            <tr>
                <td>{{ $lease->store_number ?: 'N/A' }}</td>
                <td>{{ Str::limit($lease->apartment_address, 35) }}</td>
                <td>${{ number_format($lease->total_rent, 0) }}</td>
                <td>{{ $lease->number_of_AT }}</td>
                <td>{{ $lease->has_car }}</td>
                <td>{{ ($lease->is_family === 'Yes' || $lease->is_family === 'yes') ? '✓' : '✗' }}</td>
                <td>{{ Str::limit($lease->lease_holder, 20) }}</td>
                <td>{{ $lease->expiration_date ? $lease->expiration_date->format('M Y') : 'N/A' }}</td>
                <td class="{{
                        Str::contains($lease->expiration_warning ?? '', 'Expired') ? 'status-expired' :
                        ($lease->expiration_warning ? 'status-warning' : 'status-active')
                    }}">
                    {{ $lease->expiration_warning ?: 'Active' }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Footer -->
<div class="footer">
    <p>© {{ date('Y') }} PNE Apartment Lease Management System</p>
</div>
</body>
</html>
