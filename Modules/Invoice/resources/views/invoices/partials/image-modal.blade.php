<!-- Image Generation Modal -->
<div id="imageModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-[9998]" aria-hidden="true" onclick="console.log('🎭 Backdrop clicked'); closeImageModal();"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full z-[9999]">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-black-900" id="modal-title">Invoice Preview</h3>
                    <button type="button" onclick="closeImageModal()" class="text-black-400 hover:text-black-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Invoice Content for Screenshot -->
                <div id="invoice-content" class="bg-white p-8" style="width: 850px; margin: 0 auto;">
                    <!-- Invoice Header -->
                    <div class="mb-8">
                        <h1 style="font-family: 'Oswald', sans-serif; font-size: 52px; font-weight: 700; color: #2d3748; letter-spacing: 2px; margin-bottom: 25px;">INVOICE</h1>
                        <div style="font-size: 14px; line-height: 1.6; color: #2d3748;">
                            <strong>{{ $technician->name ?? 'Technician' }}</strong><br>
                            {{ config('app.name') }}<br>
                            Maintenance Services
                        </div>
                    </div>

                    <!-- Info Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-bottom: 20px;">
                        <div>
                            <h3 style="font-size: 13px; font-weight: 700; color: #2d3748; margin-bottom: 8px;">BILL TO</h3>
                            <p style="margin: 0;"><strong>{{ $store->name }}</strong></p>
                            <p style="margin: 0;">Store #{{ $store->store_number }}</p>
                            @if($store->address)
                            <p style="margin: 0;">{{ $store->address }}</p>
                            @endif
                        </div>
                        
                        <div>
                            <h3 style="font-size: 13px; font-weight: 700; color: #2d3748; margin-bottom: 8px;">SHIP TO</h3>
                            <p style="margin: 0;"><strong>{{ $store->name }}</strong></p>
                            <p style="margin: 0;">Store #{{ $store->store_number }}</p>
                        </div>
                        
                        <div>
                            <h3 style="font-size: 13px; font-weight: 700; color: #2d3748; margin-bottom: 8px;">INVOICE #</h3>
                            <p style="margin: 0; font-weight: 600;">{{ $invoice->invoice_number }}</p>
                            <h3 style="font-size: 13px; font-weight: 700; color: #2d3748; margin-top: 10px; margin-bottom: 8px;">INVOICE DATE</h3>
                            <p style="margin: 0; font-weight: 600;">{{ $invoice->created_at->format('m/d/Y') }}</p>
                            <h3 style="font-size: 13px; font-weight: 700; color: #2d3748; margin-top: 10px; margin-bottom: 8px;">PERIOD</h3>
                            <p style="margin: 0; font-weight: 600;">{{ $invoice->period_display }}</p>
                        </div>
                    </div>

                    <!-- Stars Separator -->
                    <div style="text-align: center; margin: 25px 0; font-size: 18px; letter-spacing: 8px; color: #2d3748;">
                        ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★ ★
                    </div>

                    <!-- Invoice Table -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                        <thead style="border-top: 2px solid #e53e3e; border-bottom: 2px solid #e53e3e;">
                            <tr>
                                <th style="padding: 12px 10px; text-align: center; font-size: 13px; font-weight: 700; color: #2d3748;">QTY</th>
                                <th style="padding: 12px 10px; text-align: left; font-size: 13px; font-weight: 700; color: #2d3748;">DESCRIPTION</th>
                                <th style="padding: 12px 10px; text-align: right; font-size: 13px; font-weight: 700; color: #2d3748;">UNIT PRICE</th>
                                <th style="padding: 12px 10px; text-align: right; font-size: 13px; font-weight: 700; color: #2d3748;">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Labor Section -->
                            <tr>
                                <td colspan="4" style="background: #edf2f7; font-weight: 700; padding: 10px 20px; border-bottom: 1px solid #e2e8f0;">
                                    <strong>1. LABOR COST</strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; text-align: center; font-weight: 600; border-bottom: 1px solid #e2e8f0;">1</td>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">{{ $technician->name }} - {{ number_format($invoice->labor_hours, 2) }} hours @ ${{ number_format($technician->hourly_pay ?? 50, 2) }}/hr</td>
                                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($technician->hourly_pay ?? 50, 2) }}</td>
                                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($invoice->labor_cost, 2) }}</td>
                            </tr>
                            
                            <!-- Materials Section -->
                            <tr>
                                <td colspan="4" style="background: #edf2f7; font-weight: 700; padding: 10px 20px; border-bottom: 1px solid #e2e8f0;">
                                    <strong>2. TECHNICIAN MATERIALS</strong>
                                </td>
                            </tr>
                            @if($materials->count() > 0)
                                @foreach($materials as $material)
                                <tr>
                                    <td style="padding: 10px; text-align: center; font-weight: 600; border-bottom: 1px solid #e2e8f0;">1</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">{{ $material->item_name }}</td>
                                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($material->cost, 2) }}</td>
                                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($material->cost, 2) }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" style="padding: 10px; text-align: center; color: #718096; font-style: italic; border-bottom: 1px solid #e2e8f0;">No materials purchased for this period</td>
                                </tr>
                            @endif
                            
                            <!-- Equipment Section -->
                            <tr>
                                <td colspan="4" style="background: #edf2f7; font-weight: 700; padding: 10px 20px; border-bottom: 1px solid #e2e8f0;">
                                    <strong>3. ADMIN EQUIPMENT PURCHASES</strong>
                                </td>
                            </tr>
                            @if($equipment_items->count() > 0)
                                @foreach($equipment_items as $item)
                                <tr>
                                    <td style="padding: 10px; text-align: center; font-weight: 600; border-bottom: 1px solid #e2e8f0;">{{ $item->quantity }}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">{{ $item->item_name }} - {{ $item->company_name }}</td>
                                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($item->unit_cost, 2) }}</td>
                                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($item->total_cost, 2) }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" style="padding: 10px; text-align: center; color: #718096; font-style: italic; border-bottom: 1px solid #e2e8f0;">No equipment purchases for this period</td>
                                </tr>
                            @endif
                            
                            <!-- Mileage & Driving Time Section -->
                            <tr>
                                <td colspan="4" style="background: #edf2f7; font-weight: 700; padding: 10px 20px; border-bottom: 1px solid #e2e8f0;">
                                    <strong>4. MILEAGE & DRIVING TIME</strong>
                                </td>
                            </tr>
                            <!-- Gas Mileage -->
                            <tr>
                                <td style="padding: 10px; text-align: center; font-weight: 600; border-bottom: 1px solid #e2e8f0;">1</td>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">Gas Mileage Payment - {{ number_format($invoice->total_miles, 2) }} miles (allocated to this store)</td>
                                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($invoice->mileage_cost, 2) }}</td>
                                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($invoice->mileage_cost, 2) }}</td>
                            </tr>
                            
                            <!-- Distance Traveled (if available) -->
                            @if($invoice->total_distance_miles > 0)
                            <tr>
                                <td style="padding: 10px; text-align: center; font-weight: 600; border-bottom: 1px solid #e2e8f0;">-</td>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; color: #718096; font-style: italic;">Distance Traveled to Store - {{ number_format($invoice->total_distance_miles, 2) }} miles (odometer reading)</td>
                                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0; color: #718096;">Calculated</td>
                                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0; color: #718096;">-</td>
                            </tr>
                            @endif
                            
                            <!-- Driving Time (if available) -->
                            @if($invoice->driving_time_hours > 0)
                            <tr>
                                <td style="padding: 10px; text-align: center; font-weight: 600; border-bottom: 1px solid #e2e8f0;">1</td>
                                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">Driving Time - {{ number_format($invoice->driving_time_hours, 2) }} hours total @ ${{ number_format($invoice->driving_time_payment / $invoice->driving_time_hours, 2) }}/hr</td>
                                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($invoice->driving_time_payment / $invoice->driving_time_hours, 2) }}</td>
                                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">{{ number_format($invoice->driving_time_payment, 2) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div style="margin-left: auto; width: 300px; margin-bottom: 40px;">
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-top: 1px solid #cbd5e0; margin-top: 10px; padding-top: 12px;">
                            <span style="color: #4a5568;">Subtotal</span>
                            <span style="font-weight: 600; color: #2d3748;">{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px;">
                            <span style="color: #4a5568;">Sales Tax {{ $invoice->tax_rate }}%</span>
                            <span style="font-weight: 600; color: #2d3748;">{{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 12px 0; font-size: 18px; font-weight: 700; border-top: 2px solid #2d3748; margin-top: 10px;">
                            <span>TOTAL</span>
                            <span style="font-size: 24px; color: #2d3748;">${{ number_format($invoice->grand_total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Signature -->
                    <div style="margin-bottom: 40px;">
                        <div style="font-family: 'Brush Script MT', cursive; font-size: 48px; color: #2d3748; margin-bottom: 10px;">
                            {{ $technician->name }}
                        </div>
                    </div>

                    <!-- Thank You -->
                    <div style="font-family: 'Brush Script MT', cursive; font-size: 56px; color: #4299e1; margin-bottom: 30px;">
                        Thank you
                    </div>

                    <!-- Terms -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                        <div></div>
                        <div>
                            <h4 style="font-size: 14px; font-weight: 700; color: #e53e3e; margin-bottom: 10px; letter-spacing: 1px;">TERMS & CONDITIONS</h4>
                            <p style="font-size: 12px; line-height: 1.8; color: #4a5568; margin-bottom: 5px;">Payment is due within 15 days</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="downloadInvoiceImage()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Image
                </button>
                <button type="button" onclick="closeImageModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>



<script>
console.log('📦 Image Modal HTML loaded');
</script>
