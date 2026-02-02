@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('invoice.email-templates.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-4">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Templates
        </a>
        <h1 class="text-3xl font-bold text-black-900">Create Email Template</h1>
        <p class="mt-2 text-sm text-black-600">Create a new invoice email template</p>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('invoice.email-templates.store') }}" method="POST">
            @csrf

            <!-- Template Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Template Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                       placeholder="e.g., Default Invoice Email">
                <p class="mt-1 text-xs text-gray-500">A descriptive name for this template</p>
            </div>

            <!-- Subject Line -->
            <div class="mb-6">
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Subject <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="subject" 
                       name="subject" 
                       value="{{ old('subject', 'Invoice [INVOICE #] - [STORE NAME]') }}"
                       required
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                       placeholder="Invoice [INVOICE #] - [STORE NAME]">
                <p class="mt-1 text-xs text-gray-500">
                    Use placeholders: <code class="bg-gray-100 px-1 rounded">[INVOICE #]</code>, 
                    <code class="bg-gray-100 px-1 rounded">[STORE NAME]</code>, 
                    <code class="bg-gray-100 px-1 rounded">[PERIOD]</code>, 
                    <code class="bg-gray-100 px-1 rounded">[TECHNICIAN]</code>
                </p>
            </div>

            <!-- Email Body -->
            <div class="mb-6">
                <label for="body" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Message <span class="text-red-500">*</span>
                </label>
                
                <!-- Formatting Toolbar -->
                <div class="mb-2 flex gap-2 p-2 bg-gray-100 rounded-lg border border-gray-300">
                    <button type="button" onclick="formatText('bold')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50" title="Bold">
                        <strong>B</strong>
                    </button>
                    <button type="button" onclick="formatText('italic')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50" title="Italic">
                        <em>I</em>
                    </button>
                    <button type="button" onclick="formatText('underline')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50" title="Underline">
                        <u>U</u>
                    </button>
                    <div class="border-l border-gray-300 mx-2"></div>
                    <button type="button" onclick="insertVariable('invoice_number')" class="px-3 py-1 bg-orange-50 border border-orange-300 rounded hover:bg-orange-100 text-sm">
                        Invoice #
                    </button>
                    <button type="button" onclick="insertVariable('store_name')" class="px-3 py-1 bg-orange-50 border border-orange-300 rounded hover:bg-orange-100 text-sm">
                        Store Name
                    </button>
                    <button type="button" onclick="insertVariable('period')" class="px-3 py-1 bg-orange-50 border border-orange-300 rounded hover:bg-orange-100 text-sm">
                        Period
                    </button>
                    <button type="button" onclick="insertVariable('technician_name')" class="px-3 py-1 bg-orange-50 border border-orange-300 rounded hover:bg-orange-100 text-sm">
                        Technician
                    </button>
                </div>
                
                <!-- Rich Text Editor -->
                <div id="editor" 
                     contenteditable="true"
                     class="block w-full min-h-[200px] rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 p-4 bg-white"
                     style="border: 1px solid #d1d5db;">
                    <p>Dear Valued Customer,</p>
                    <p><br></p>
                    <p>Please find your invoice details below for the maintenance services provided during the period <strong>[PERIOD]</strong>.</p>
                    <p><br></p>
                    <p>We appreciate your business and look forward to serving you again.</p>
                    <p><br></p>
                    <p>If you have any questions about this invoice, please don't hesitate to contact us.</p>
                    <p><br></p>
                    <p>Best regards,<br><strong>[TECHNICIAN]</strong><br>Maintenance Services Team</p>
                </div>
                
                <!-- Hidden textarea to store HTML -->
                <textarea id="body" 
                          name="body" 
                          required
                          class="hidden">{{ old('body', '<p>Dear Valued Customer,</p><p><br></p><p>Please find your invoice details below for the maintenance services provided during the period <strong>[PERIOD]</strong>.</p><p><br></p><p>We appreciate your business and look forward to serving you again.</p><p><br></p><p>If you have any questions about this invoice, please don\'t hesitate to contact us.</p><p><br></p><p>Best regards,<br><strong>[TECHNICIAN]</strong><br>Maintenance Services Team</p>') }}</textarea>
                
                <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>💡 Tip:</strong> Use the orange buttons above to insert placeholders. They will be replaced with actual data when sending:
                    </p>
                    <ul class="mt-2 text-xs text-blue-700 space-y-1 ml-4">
                        <li><strong>[INVOICE #]</strong> → Invoice number (e.g., INV-2024-001)</li>
                        <li><strong>[STORE NAME]</strong> → Store name (e.g., ABC Store)</li>
                        <li><strong>[PERIOD]</strong> → Invoice period (e.g., Jan 1-15, 2024)</li>
                        <li><strong>[TECHNICIAN]</strong> → Technician name (e.g., John Smith)</li>
                    </ul>
                </div>
            </div>

            <!-- Set as Default -->
            <div class="mb-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_default" 
                           name="is_default" 
                           value="1"
                           {{ old('is_default') ? 'checked' : '' }}
                           class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                    <label for="is_default" class="ml-2 block text-sm text-gray-700">
                        Set as default template
                    </label>
                </div>
                <p class="mt-1 ml-6 text-xs text-gray-500">
                    The default template will be pre-selected when sending invoices
                </p>
            </div>

            <!-- Preview Section -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Preview</h3>
                <div id="preview" class="prose prose-sm max-w-none bg-white p-4 rounded border border-gray-200">
                    <p>Email preview will appear here...</p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('invoice.email-templates.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Create Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize editor with existing content
document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('editor');
    const hiddenTextarea = document.getElementById('body');
    
    // Load existing content if any
    if (hiddenTextarea.value) {
        editor.innerHTML = hiddenTextarea.value;
    }
    
    // Update hidden textarea when editor changes
    editor.addEventListener('input', function() {
        hiddenTextarea.value = editor.innerHTML;
        updatePreview();
    });
    
    // Initial preview
    updatePreview();
});

// Format text (bold, italic, underline)
function formatText(command) {
    document.execCommand(command, false, null);
    document.getElementById('editor').focus();
}

// Insert variable placeholder
function insertVariable(variable) {
    const editor = document.getElementById('editor');
    const variableMap = {
        'invoice_number': '[INVOICE #]',
        'store_name': '[STORE NAME]',
        'period': '[PERIOD]',
        'technician_name': '[TECHNICIAN]'
    };
    
    const placeholder = variableMap[variable];
    
    // Insert at cursor position
    editor.focus();
    document.execCommand('insertHTML', false, '<strong style="color: #f97316;">' + placeholder + '</strong>');
    
    // Update hidden textarea
    document.getElementById('body').value = editor.innerHTML;
    updatePreview();
}

// Update preview
function updatePreview() {
    const editor = document.getElementById('editor');
    const preview = document.getElementById('preview');
    
    // Replace placeholders with example data for preview
    let previewContent = editor.innerHTML;
    previewContent = previewContent.replace(/\[INVOICE #\]/g, '<span style="background: #fef3c7; padding: 2px 4px; border-radius: 3px;">INV-2024-001</span>');
    previewContent = previewContent.replace(/\[STORE NAME\]/g, '<span style="background: #fef3c7; padding: 2px 4px; border-radius: 3px;">ABC Store</span>');
    previewContent = previewContent.replace(/\[PERIOD\]/g, '<span style="background: #fef3c7; padding: 2px 4px; border-radius: 3px;">Jan 1-15, 2024</span>');
    previewContent = previewContent.replace(/\[TECHNICIAN\]/g, '<span style="background: #fef3c7; padding: 2px 4px; border-radius: 3px;">John Smith</span>');
    
    preview.innerHTML = previewContent || '<p class="text-gray-400">Email preview will appear here...</p>';
}
</script>
@endsection
