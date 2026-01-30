<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #f97316;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #f97316;
            margin: 0;
            font-size: 32px;
        }
        .invoice-info {
            background-color: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 15px;
            margin-bottom: 25px;
        }
        .invoice-info p {
            margin: 5px 0;
        }
        .invoice-info strong {
            color: #f97316;
        }
        .content {
            margin-bottom: 30px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .invoice-table th {
            background-color: #f97316;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .invoice-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .invoice-table tr:hover {
            background-color: #f9fafb;
        }
        .section-header {
            background-color: #f3f4f6;
            font-weight: bold;
            padding: 10px 12px;
        }
        .total-row {
            background-color: #fff7ed;
            font-weight: bold;
            font-size: 18px;
        }
        .total-row td {
            padding: 15px 12px;
            border-top: 2px solid #f97316;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #f97316;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #ea580c;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <p style="margin: 10px 0 0 0; color: #6b7280;">{{ config('app.name') }}</p>
        </div>

        <!-- Custom Message from Template -->
        <div class="content">
            {!! $template->body !!}
        </div>

        <!-- Invoice Information -->
        <div class="invoice-info">
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Store:</strong> {{ $store->store_number }} - {{ $store->name }}</p>
            <p><strong>Technician:</strong> {{ $technician->name }}</p>
            <p><strong>Period:</strong> {{ $invoice->period_display }}</p>
            <p><strong>Invoice Date:</strong> {{ $invoice->created_at->format('F d, Y') }}</p>
        </div>

        <!-- Invoice Details Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <!-- Labor -->
                <tr class="section-header">
                    <td colspan="2">1. LABOR COST</td>
                </tr>
                <tr>
                    <td>{{ $technician->name }} - {{ number_format($invoice->labor_hours, 2) }} hours @ ${{ number_format($technician->hourly_pay ?? 50, 2) }}/hr</td>
                    <td style="text-align: right;">${{ number_format($invoice->labor_cost, 2) }}</td>
                </tr>

                <!-- Materials -->
                <tr class="section-header">
                    <td colspan="2">2. TECHNICIAN MATERIALS</td>
                </tr>
                @forelse($materials as $material)
                <tr>
                    <td>{{ $material->item_name }}</td>
                    <td style="text-align: right;">${{ number_format($material->cost, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #9ca3af; font-style: italic;">No materials purchased</td>
                </tr>
                @endforelse

                <!-- Equipment -->
                <tr class="section-header">
                    <td colspan="2">3. ADMIN EQUIPMENT PURCHASES</td>
                </tr>
                @forelse($equipment_items as $item)
                <tr>
                    <td>{{ $item->item_name }} (Qty: {{ $item->quantity }}) - {{ $item->company_name }}</td>
                    <td style="text-align: right;">${{ number_format($item->total_cost, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #9ca3af; font-style: italic;">No equipment purchases</td>
                </tr>
                @endforelse

                <!-- Mileage & Driving Time -->
                <tr class="section-header">
                    <td colspan="2">4. MILEAGE & DRIVING TIME</td>
                </tr>
                <tr>
                    <td>Gas Mileage Payment - {{ number_format($invoice->total_miles, 2) }} miles (allocated to this store)</td>
                    <td style="text-align: right;">${{ number_format($invoice->mileage_cost, 2) }}</td>
                </tr>
                @if($invoice->driving_time_hours > 0)
                <tr>
                    <td>Driving Time - {{ number_format($invoice->driving_time_hours, 2) }} hours total</td>
                    <td style="text-align: right;">${{ number_format($invoice->driving_time_payment, 2) }}</td>
                </tr>
                @endif

                <!-- Totals -->
                <tr>
                    <td style="text-align: right; padding-top: 20px;"><strong>Subtotal:</strong></td>
                    <td style="text-align: right; padding-top: 20px;"><strong>${{ number_format($invoice->subtotal, 2) }}</strong></td>
                </tr>
                <tr>
                    <td style="text-align: right;"><strong>Tax ({{ $invoice->tax_rate }}%):</strong></td>
                    <td style="text-align: right;"><strong>${{ number_format($invoice->tax_amount, 2) }}</strong></td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: right;">GRAND TOTAL:</td>
                    <td style="text-align: right;">${{ number_format($invoice->grand_total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top: 10px;">
                This is an automated email. Please do not reply to this message.<br>
                If you have any questions, please contact us.
            </p>
            <p style="margin-top: 15px; font-size: 12px;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
