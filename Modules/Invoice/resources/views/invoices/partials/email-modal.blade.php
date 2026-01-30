<!-- Email Modal -->
<div id="emailModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-[9998]" aria-hidden="true" onclick="console.log('🎭 Email Backdrop clicked'); closeEmailModal();"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-[9999]">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-black-900">Send Invoice via Email</h3>
                    <button type="button" onclick="closeEmailModal()" class="text-black-400 hover:text-black-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="emailForm" onsubmit="sendInvoiceEmail(event)">
                    <!-- Recipient Selection -->
                    <div class="mb-4">
                        <label for="recipient_select" class="block text-sm font-medium text-gray-700 mb-2">Select Recipient</label>
                        <select id="recipient_select" onchange="updateEmailField()"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">-- Select or enter custom email --</option>
                            @foreach($recipients as $recipient)
                                <option value="{{ $recipient->email }}" data-name="{{ $recipient->name }}" {{ $recipient->is_default ? 'selected' : '' }}>
                                    {{ $recipient->name }} ({{ $recipient->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Email Input -->
                    <div class="mb-4">
                        <label for="email_input" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email_input" name="email" required
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               placeholder="recipient@example.com"
                               value="{{ $recipients->where('is_default', true)->first()->email ?? '' }}">
                    </div>

                    <!-- Template Selection -->
                    <div class="mb-4">
                        <label for="template_select" class="block text-sm font-medium text-gray-700 mb-2">Email Template</label>
                        <select id="template_select" name="template_id" onchange="updateTemplatePreview()"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ $template->is_default ? 'selected' : '' }}>
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Template Preview -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message Preview</label>
                        <div id="template_preview" class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-sm">
                            @if($templates->count() > 0)
                                {!! $templates->where('is_default', true)->first()->body ?? $templates->first()->body !!}
                            @else
                                <p>Dear Customer,</p>
                                <p>Please find attached your invoice for the period {{ $invoice->period_display }}.</p>
                                <p>Invoice Number: {{ $invoice->invoice_number }}</p>
                                <p>Total Amount: ${{ number_format($invoice->grand_total, 2) }}</p>
                                <p>Thank you for your business!</p>
                            @endif
                        </div>
                    </div>

                    <!-- Invoice Summary -->
                    <div class="bg-blue-50 rounded-lg p-4 mb-4 border border-blue-200">
                        <h4 class="font-semibold text-blue-900 mb-2">Invoice Summary</h4>
                        <div class="text-sm text-blue-800 space-y-1">
                            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                            <p><strong>Store:</strong> {{ $store->name }}</p>
                            <p><strong>Period:</strong> {{ $invoice->period_display }}</p>
                            <p><strong>Total Amount:</strong> ${{ number_format($invoice->grand_total, 2) }}</p>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEmailModal()"
                                class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                            Cancel
                        </button>
                        <button type="submit" id="sendEmailBtn"
                                class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Send Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
console.log('📦 Email Modal HTML loaded');
</script>
