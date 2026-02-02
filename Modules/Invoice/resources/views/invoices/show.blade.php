@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('invoice.invoices.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-4">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Invoices
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Invoice Details</h1>
                <p class="mt-2 text-sm text-black-600">{{ $invoice->invoice_number }} - {{ $store->name }}</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="console.log('🔘 Generate Image button clicked'); openImageModal();" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Generate Image
                </button>
                <button type="button" onclick="console.log('🔘 Send Email button clicked'); openEmailModal();"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Send Email
                </button>
            </div>
        </div>
    </div>

    <!-- Invoice Preview Card -->
    <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-6">
        <!-- Store & Period Info -->
        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-orange-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-black-600">Store</p>
                    <p class="text-lg font-bold text-black-900">{{ $store->store_number }} - {{ $store->name }}</p>
                    @if($store->address)
                    <p class="text-sm text-black-600">{{ $store->address }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-black-600">Period</p>
                    <p class="text-lg font-bold text-black-900">{{ $invoice->period_display }}</p>
                    <p class="text-sm text-black-600">{{ $invoice->days_in_period }} days</p>
                </div>
            </div>
        </div>

        <!-- Invoice Body -->
        <div class="p-6 space-y-6">
            <!-- 1. Labor Costs -->
            <div>
                <h3 class="text-lg font-bold text-black-900 mb-3 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 text-sm font-bold mr-2">1</span>
                    Labor Costs
                </h3>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex justify-between items-center py-2">
                        <div>
                            <p class="font-semibold text-black-900">{{ $technician->name }}</p>
                            <p class="text-sm text-black-600">{{ number_format($invoice->labor_hours, 2) }} hours @ ${{ number_format($technician->hourly_pay ?? 50, 2) }}/hr</p>
                        </div>
                        <p class="text-lg font-bold text-blue-600">${{ number_format($invoice->labor_cost, 2) }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-3 mt-3 border-t-2 border-blue-300">
                        <p class="font-bold text-black-900">Labor Subtotal</p>
                        <p class="text-xl font-bold text-blue-600">${{ number_format($invoice->labor_cost, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- 2. Technician Materials -->
            <div>
                <h3 class="text-lg font-bold text-black-900 mb-3 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 text-sm font-bold mr-2">2</span>
                    Technician Materials
                </h3>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    @forelse($materials as $material)
                    <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-green-200' : '' }}">
                        <p class="text-black-900">{{ $material->item_name }}</p>
                        <p class="font-semibold text-green-600">${{ number_format($material->cost, 2) }}</p>
                    </div>
                    @empty
                    <p class="text-sm text-black-500 text-center py-2">No materials purchased for this period</p>
                    @endforelse
                    <div class="flex justify-between items-center pt-3 mt-3 border-t-2 border-green-300">
                        <p class="font-bold text-black-900">Materials Subtotal</p>
                        <p class="text-xl font-bold text-green-600">${{ number_format($invoice->materials_cost, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- 3. Admin Equipment -->
            <div>
                <h3 class="text-lg font-bold text-black-900 mb-3 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 text-sm font-bold mr-2">3</span>
                    Admin Equipment Purchases
                </h3>
                <div class="bg-gradient-to-br from-purple-50 to-white rounded-lg p-4 border-2 border-purple-200">
                    @forelse($equipment_items as $item)
                    <div class="py-3 {{ !$loop->last ? 'border-b border-purple-200' : '' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-semibold text-black-900">{{ $item->item_name }}</p>
                                <div class="flex items-center gap-3 mt-1">
                                    <p class="text-xs text-black-600">
                                        <span class="font-medium">Qty:</span> {{ $item->quantity }}
                                    </p>
                                    <p class="text-xs text-black-600">
                                        <span class="font-medium">Unit:</span> ${{ number_format($item->unit_cost, 2) }}
                                    </p>
                                    <p class="text-xs text-purple-700 font-medium">
                                        {{ $item->company_name }}
                                    </p>
                                </div>
                                <p class="text-xs text-black-500 mt-1">
                                    Payment Date: {{ \Carbon\Carbon::parse($item->payment_date)->format('M d, Y') }} | Payment #{{ $item->payment_id }}
                                    @if($item->maintenance_request_id)
                                        <span class="ml-2 text-blue-600 font-medium">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            Task #{{ $item->maintenance_request_id }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <p class="text-lg font-bold text-purple-600 ml-4">${{ number_format($item->total_cost, 2) }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-black-500 text-center py-4">No equipment purchases for this period</p>
                    @endforelse
                    <div class="flex justify-between items-center pt-4 mt-4 border-t-2 border-purple-300">
                        <p class="font-bold text-black-900">Equipment Subtotal</p>
                        <p class="text-xl font-bold text-purple-600">${{ number_format($invoice->equipment_cost, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- 4. Mileage & Driving Time -->
            <div>
                <h3 class="text-lg font-bold text-black-900 mb-3 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-600 text-sm font-bold mr-2">4</span>
                    Mileage & Driving Time
                </h3>
                <div class="bg-orange-50 rounded-lg p-4 border border-orange-200 space-y-3">
                    <!-- Gas Mileage -->
                    <div class="flex justify-between items-center pb-3 border-b border-orange-200">
                        <div>
                            <p class="font-semibold text-black-900">Gas Mileage</p>
                            <p class="text-sm text-black-600">{{ number_format($invoice->total_miles, 2) }} miles driven</p>
                        </div>
                        <p class="text-lg font-bold text-orange-600">${{ number_format($invoice->mileage_cost, 2) }}</p>
                    </div>
                    
                    <!-- Driving Distance (from odometer) -->
                    @if($invoice->total_distance_miles > 0)
                    <div class="flex justify-between items-center pb-3 border-b border-orange-200">
                        <div>
                            <p class="font-semibold text-black-900">Distance Traveled</p>
                            <p class="text-sm text-black-600">{{ number_format($invoice->total_distance_miles, 2) }} miles (odometer)</p>
                        </div>
                        <p class="text-sm text-black-600">Calculated</p>
                    </div>
                    @endif
                    
                    <!-- Driving Time -->
                    @if($invoice->driving_time_hours > 0)
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold text-black-900">Driving Time</p>
                            <p class="text-sm text-black-600">{{ number_format($invoice->driving_time_hours, 2) }} hours @ ${{ number_format($invoice->driving_time_payment / $invoice->driving_time_hours, 2) }}/hr</p>
                        </div>
                        <p class="text-lg font-bold text-orange-600">${{ number_format($invoice->driving_time_payment, 2) }}</p>
                    </div>
                    @endif
                    
                    <!-- Subtotal -->
                    <div class="flex justify-between items-center pt-3 border-t-2 border-orange-300">
                        <p class="font-bold text-black-900">Mileage & Driving Subtotal</p>
                        <p class="text-xl font-bold text-orange-600">${{ number_format($invoice->mileage_cost + $invoice->driving_time_payment, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg p-6 border-2 border-orange-300">
                <div class="space-y-3">
                    <div class="flex justify-between text-lg">
                        <span class="text-black-700">Subtotal</span>
                        <span class="font-semibold text-black-900">${{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg">
                        <span class="text-black-700">Tax ({{ $invoice->tax_rate }}%)</span>
                        <span class="font-semibold text-black-900">${{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-2xl pt-3 border-t-2 border-orange-400">
                        <span class="font-bold text-black-900">Grand Total</span>
                        <span class="font-bold text-orange-600">${{ number_format($invoice->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Generation Modal -->
@include('invoice::invoices.partials.image-modal')

<!-- Email Modal -->
@include('invoice::invoices.partials.email-modal')
@endsection

@push('scripts')
<!-- dom-to-image Library (better support for modern CSS) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>

<script>
console.log('🚀 Invoice Scripts Loaded');

// Modal Functions
function openImageModal() {
    console.log('📸 openImageModal() called');
    const modal = document.getElementById('imageModal');
    console.log('Modal element:', modal);
    
    if (modal) {
        console.log('✅ Modal found, removing hidden class');
        console.log('Modal classes before:', modal.className);
        modal.classList.remove('hidden');
        console.log('Modal classes after:', modal.className);
        document.body.style.overflow = 'hidden';
        console.log('Body overflow set to hidden');
    } else {
        console.error('❌ Modal element not found!');
    }
}

function closeImageModal() {
    console.log('🔒 closeImageModal() called');
    const modal = document.getElementById('imageModal');
    if (modal) {
        console.log('✅ Closing modal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function openEmailModal() {
    console.log('📧 openEmailModal() called');
    const modal = document.getElementById('emailModal');
    console.log('Email Modal element:', modal);
    
    if (modal) {
        console.log('✅ Email Modal found, removing hidden class');
        console.log('Modal classes before:', modal.className);
        modal.classList.remove('hidden');
        console.log('Modal classes after:', modal.className);
        document.body.style.overflow = 'hidden';
        console.log('Body overflow set to hidden');
    } else {
        console.error('❌ Email Modal element not found!');
    }
}

function closeEmailModal() {
    console.log('🔒 closeEmailModal() called');
    const modal = document.getElementById('emailModal');
    if (modal) {
        console.log('✅ Closing email modal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Image Download Function
function downloadInvoiceImage() {
    console.log('🖼️ downloadInvoiceImage() called');
    const element = document.getElementById('invoice-content');
    const button = event.target;
    
    if (!element) {
        console.error('❌ Invoice content element not found!');
        alert('Error: Invoice content not found');
        return;
    }
    
    console.log('✅ Invoice content found, starting generation...');
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';
    
    // Use dom-to-image instead of html2canvas for better CSS support
    domtoimage.toPng(element, {
        quality: 1,
        width: element.scrollWidth * 2,
        height: element.scrollHeight * 2,
        style: {
            transform: 'scale(2)',
            transformOrigin: 'top left',
            width: element.scrollWidth + 'px',
            height: element.scrollHeight + 'px'
        }
    })
    .then(function (dataUrl) {
        console.log('✅ Image generated successfully');
        const link = document.createElement('a');
        link.download = 'Invoice-{{ $invoice->invoice_number }}.png';
        link.href = dataUrl;
        link.click();
        console.log('✅ Download triggered');
        
        button.disabled = false;
        button.innerHTML = '<svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg> Download Image';
    })
    .catch(function (error) {
        console.error('❌ Error generating image:', error);
        alert('Error generating image. Please try again.');
        button.disabled = false;
        button.innerHTML = '<svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg> Download Image';
    });
}

// Email Functions
function updateEmailField() {
    const select = document.getElementById('recipient_select');
    const emailInput = document.getElementById('email_input');
    if (select && emailInput && select.value) {
        emailInput.value = select.value;
    }
}

function updateTemplatePreview() {
    const templateId = document.getElementById('template_select').value;
    // In a real implementation, you would fetch the template content via AJAX
    // For now, we'll keep the existing preview
}

function sendInvoiceEmail(event) {
    event.preventDefault();
    console.log('📧 sendInvoiceEmail() called');
    
    const form = event.target;
    const button = document.getElementById('sendEmailBtn');
    const email = document.getElementById('email_input').value;
    const templateId = document.getElementById('template_select').value;
    
    console.log('📧 Email:', email);
    console.log('📧 Template ID:', templateId);
    
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';
    
    console.log('📧 Sending request to:', '{{ route("invoice.invoices.send-email", $invoice->id) }}');
    
    fetch('{{ route("invoice.invoices.send-email", $invoice->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            email: email,
            template_id: templateId
        })
    })
    .then(response => {
        console.log('📧 Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('📧 Response data:', data);
        if (data.success) {
            alert('✅ Invoice sent successfully to ' + email + '!');
            closeEmailModal();
            window.location.reload();
        } else {
            alert('❌ Error sending invoice: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('❌ Error:', error);
        alert('❌ Error sending invoice. Please check console for details.');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '<svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg> Send Invoice';
    });
}

// Close modals when clicking outside or pressing Escape
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM Content Loaded');
    
    const imageModal = document.getElementById('imageModal');
    const emailModal = document.getElementById('emailModal');
    
    console.log('🔍 Checking modals on page load:');
    console.log('  - Image Modal:', imageModal ? '✅ Found' : '❌ Not Found');
    console.log('  - Email Modal:', emailModal ? '✅ Found' : '❌ Not Found');
    
    if (imageModal) {
        console.log('🎯 Adding click listener to image modal');
        imageModal.addEventListener('click', function(e) {
            console.log('🖱️ Image modal clicked, target:', e.target);
            console.log('🖱️ This element:', this);
            if (e.target === this) {
                console.log('✅ Clicked on backdrop, closing modal');
                closeImageModal();
            } else {
                console.log('⚠️ Clicked inside modal content, not closing');
            }
        });
    }
    
    if (emailModal) {
        console.log('🎯 Adding click listener to email modal');
        emailModal.addEventListener('click', function(e) {
            console.log('🖱️ Email modal clicked, target:', e.target);
            console.log('🖱️ This element:', this);
            if (e.target === this) {
                console.log('✅ Clicked on backdrop, closing modal');
                closeEmailModal();
            } else {
                console.log('⚠️ Clicked inside modal content, not closing');
            }
        });
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            console.log('⌨️ Escape key pressed, closing all modals');
            closeImageModal();
            closeEmailModal();
        }
    });
    
    console.log('✅ All event listeners attached');
});
</script>
@endpush
