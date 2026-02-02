<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Invoice</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@700&family=Roboto:wght@300;400;500;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 850px;
            margin: 0 auto;
            background: white;
            padding: 50px 60px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        /* Header */
        .invoice-header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .invoice-title {
            font-family: 'Oswald', sans-serif;
            font-size: 52px;
            font-weight: 700;
            color: #2d3748;
            letter-spacing: 2px;
            margin-bottom: 25px;
            line-height: 1;
        }
        
        .from-section {
            font-size: 14px;
            line-height: 1.6;
            color: #2d3748;
            margin-bottom: 30px;
        }
        
        .from-section strong {
            font-weight: 700;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .info-section h3 {
            font-size: 13px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .info-section p {
            font-size: 13px;
            line-height: 1.6;
            color: #4a5568;
            margin-bottom: 3px;
        }
        
        .info-section .highlight {
            font-weight: 600;
            color: #2d3748;
        }
        
        /* Stars Separator */
        .stars-separator {
            text-align: center;
            margin: 25px 0;
            font-size: 18px;
            letter-spacing: 8px;
            color: #2d3748;
        }
        
        /* Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .invoice-table thead {
            border-top: 2px solid #e53e3e;
            border-bottom: 2px solid #e53e3e;
        }
        
        .invoice-table th {
            padding: 12px 10px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            color: #2d3748;
            letter-spacing: 0.5px;
        }
        
        .invoice-table th:last-child,
        .invoice-table td:last-child {
            text-align: right;
        }
        
        .invoice-table th:first-child {
            text-align: center;
        }
        
        .invoice-table td {
            padding: 10px;
            font-size: 13px;
            color: #2d3748;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .invoice-table td:first-child {
            text-align: center;
            font-weight: 600;
        }
        
        .invoice-table tbody tr:hover {
            background: #f7fafc;
        }
        
        /* Totals Section */
        .totals-section {
            margin-left: auto;
            width: 300px;
            margin-bottom: 40px;
        }
        
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }
        
        .totals-row.subtotal {
            border-top: 1px solid #cbd5e0;
            margin-top: 10px;
            padding-top: 12px;
        }
        
        .totals-row.total {
            border-top: 2px solid #2d3748;
            margin-top: 10px;
            padding-top: 12px;
            font-size: 18px;
            font-weight: 700;
        }
        
        .totals-row span:first-child {
            color: #4a5568;
        }
        
        .totals-row span:last-child {
            font-weight: 600;
            color: #2d3748;
        }
        
        .totals-row.total span:last-child {
            font-size: 24px;
            color: #2d3748;
        }
        
        /* Flag Watermark */
        .flag-watermark {
            position: absolute;
            right: 60px;
            bottom: 220px;
            opacity: 0.15;
            width: 400px;
            height: 200px;
            background: linear-gradient(to bottom,
                #4a5568 0%, #4a5568 40%,
                #f7fafc 40%, #f7fafc 45%,
                #e53e3e 45%, #e53e3e 50%,
                #f7fafc 50%, #f7fafc 55%,
                #e53e3e 55%, #e53e3e 60%,
                #f7fafc 60%, #f7fafc 65%,
                #e53e3e 65%, #e53e3e 100%
            );
            pointer-events: none;
            z-index: 0;
        }
        
        /* Signature */
        .signature-section {
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }
        
        .signature {
            font-family: 'Brush Script MT', cursive;
            font-size: 48px;
            color: #2d3748;
            margin-bottom: 10px;
        }
        
        /* Thank You */
        .thank-you {
            font-family: 'Brush Script MT', cursive;
            font-size: 56px;
            color: #4299e1;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }
        
        /* Footer */
        .footer-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            position: relative;
            z-index: 1;
        }
        
        .terms h4 {
            font-size: 14px;
            font-weight: 700;
            color: #e53e3e;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .terms p {
            font-size: 12px;
            line-height: 1.8;
            color: #4a5568;
            margin-bottom: 5px;
        }
        
        .terms .bank-info {
            font-weight: 600;
            color: #2d3748;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Flag Watermark -->
        <div class="flag-watermark"></div>
        
        <!-- Header -->
        <div class="invoice-header">
            <div>
                <h1 class="invoice-title">INVOICE</h1>
                <div class="from-section">
                    <strong>John Smith</strong><br>
                    4490 Oak Drive<br>
                    Albany, NY 12210
                </div>
            </div>
            <img src="{{ asset('images/invoice-logo.png') }}" alt="Company Logo" style="max-height: 150px; width: auto; margin-top: 10px;">
        </div>
        
        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-section">
                <h3>BILL TO</h3>
                <p><strong>Store #005 - Mall Rd</strong></p>
                <p>4312 Wood Road</p>
                <p>New York, NY 10031</p>
            </div>
            
            <div class="info-section">
                <h3>SHIP TO</h3>
                <p><strong>Store #005 - Mall Rd</strong></p>
                <p>2019 Redbud Drive</p>
                <p>New York, NY 10011</p>
            </div>
            
            <div class="info-section">
                <h3>INVOICE #</h3>
                <p class="highlight">INT-001</p>
                <h3 style="margin-top: 10px;">INVOICE DATE</h3>
                <p class="highlight">11/02/2025</p>
                <h3 style="margin-top: 10px;">P.O.#</h3>
                <p class="highlight">24/12/2019</p>
                <h3 style="margin-top: 10px;">DUE DATE</h3>
                <p class="highlight">26/02/2025</p>
            </div>
        </div>
        
        <!-- Stars Separator -->
        <div class="stars-separator">★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★</div>
        
        <!-- Invoice Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 60px;">QTY</th>
                    <th style="width: 50%;">DESCRIPTION</th>
                    <th style="width: 20%; text-align: right;">UNIT PRICE</th>
                    <th style="width: 20%; text-align: right;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <!-- Labor Cost Section -->
                <tr>
                    <td colspan="4" style="background: #edf2f7; font-weight: 700; padding-left: 20px;">
                        <strong>1. LABOR COST</strong>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>John Smith - 8.5 hours @ $50/hr</td>
                    <td style="text-align: right;">50.00</td>
                    <td style="text-align: right;">425.00</td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Mike Johnson - 3.25 hours @ $45/hr</td>
                    <td style="text-align: right;">45.00</td>
                    <td style="text-align: right;">146.25</td>
                </tr>
                
                <!-- Materials Section -->
                <tr>
                    <td colspan="4" style="background: #edf2f7; font-weight: 700; padding-left: 20px;">
                        <strong>2. TECHNICIAN MATERIALS</strong>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Front and rear brake cables</td>
                    <td style="text-align: right;">100.00</td>
                    <td style="text-align: right;">100.00</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>New set of pedal arms</td>
                    <td style="text-align: right;">25.00</td>
                    <td style="text-align: right;">50.00</td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>HVAC Filter Replacement</td>
                    <td style="text-align: right;">85.00</td>
                    <td style="text-align: right;">85.00</td>
                </tr>
                
                <!-- Equipment Section -->
                <tr>
                    <td colspan="4" style="background: #edf2f7; font-weight: 700; padding-left: 20px;">
                        <strong>3. ADMIN EQUIPMENT PURCHASES</strong>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Commercial Freezer - Bibline Model X500</td>
                    <td style="text-align: right;">2,850.00</td>
                    <td style="text-align: right;">2,850.00</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Stainless Steel Shelving Units</td>
                    <td style="text-align: right;">185.00</td>
                    <td style="text-align: right;">555.00</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-row subtotal">
                <span>Subtotal</span>
                <span>4,211.25</span>
            </div>
            <div class="totals-row">
                <span>Sales Tax 5.0%</span>
                <span>210.56</span>
            </div>
            <div class="totals-row total">
                <span>TOTAL</span>
                <span>$4,421.81</span>
            </div>
        </div>
        
        <!-- Signature -->
        <div class="signature-section">
            <div class="signature">John Smith</div>
        </div>
        
        <!-- Thank You -->
        <div class="thank-you">Thank you</div>
        
        <!-- Footer -->
        <div class="footer-section">
            <div></div>
            <div class="terms">
                <h4>TERMS & CONDITIONS</h4>
                <p>Payment is due within 15 days</p>
                <p style="margin-top: 10px;">Name of Bank: <span class="bank-info">First National Bank</span></p>
                <p>Account number: <span class="bank-info">1234567890</span></p>
                <p>Routing: <span class="bank-info">098765432</span></p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-print functionality (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
