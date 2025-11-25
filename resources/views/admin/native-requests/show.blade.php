@extends('layouts.app')

@section('title', 'Request #' . $request->id . ' - Admin')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Request #{{ $request->id }}</h1>
            <p class="mt-1 text-sm text-gray-600">Submitted on {{ $request->request_date->format('F d, Y') }}</p>
        </div>
        <a href="{{ route('admin.native.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            ← Back to All Requests
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content (Left) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Request Details --}}
            <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Request Details</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-semibold text-gray-700">Store:</span>
                            <p class="mt-1 text-gray-900">{{ $request->store->store_number }} - {{ $request->store->name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-700">Requester:</span>
                            <p class="mt-1 text-gray-900">{{ $request->requester->name }}</p>
                        </div>
                    </div>

                    <div>
                        <span class="text-sm font-semibold text-gray-700">Equipment with Issue:</span>
                        <p class="mt-1 text-gray-900">{{ $request->equipment_with_issue }}</p>
                    </div>

                    <div>
                        <span class="text-sm font-semibold text-gray-700">Urgency Level:</span>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold" 
                                style="background-color: {{ $request->urgencyLevel->color }}20; color: {{ $request->urgencyLevel->color }};">
                                <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $request->urgencyLevel->color }};"></span>
                                {{ $request->urgencyLevel->name }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-sm font-semibold text-gray-700">Basic Troubleshooting:</span>
                        <p class="mt-1">
                            @if($request->basic_troubleshoot_done)
                                <span class="inline-flex items-center px-2 py-1 rounded text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Completed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded text-sm font-medium bg-gray-100 text-gray-800">Not Done</span>
                            @endif
                        </p>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <span class="text-sm font-semibold text-gray-700">Description of Issue:</span>
                        <p class="mt-2 text-gray-900 bg-gray-50 rounded-lg p-4">{{ $request->description_of_issue }}</p>
                    </div>
                </div>
            </div>

            {{-- Attachments with Lightbox --}}
            @if($request->attachments->count() > 0)
                <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Attachments ({{ $request->attachments->count() }})</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            @foreach($request->attachments as $attachment)
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                    @if(Str::startsWith($attachment->mime_type, 'image/'))
                                        <div class="cursor-pointer lightbox-trigger" data-image="{{ $attachment->url }}">
                                            <img src="{{ $attachment->url }}" 
                                                 alt="{{ $attachment->file_name }}" 
                                                 class="w-full h-32 object-cover hover:opacity-90 transition-opacity"
                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-32 bg-gray-100 flex flex-col items-center justify-center\'><span class=\'text-xs text-gray-500\'>Image load failed</span></div>'">
                                        </div>
                                    @else
                                        <div class="w-full h-32 bg-gray-100 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="p-2 bg-gray-50">
                                        <p class="text-xs text-gray-600 truncate" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</p>
                                        <a href="{{ $attachment->url }}" 
                                           download 
                                           class="text-xs text-orange-600 hover:text-orange-800">Download</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar (Right) - Status Management --}}
        <div class="lg:col-span-1">
            <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200 sticky top-6">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-orange-200">
                    <h2 class="text-xl font-bold text-gray-900">Status Management</h2>
                </div>
                <form action="{{ route('admin.native.updateStatus', $request) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" id="status" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $request->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="done" {{ $request->status === 'done' ? 'selected' : '' }}>Done</option>
                            <option value="canceled" {{ $request->status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Assign To --}}
                    <div>
                        <label for="assigned_to" class="block text-sm font-semibold text-gray-700 mb-2">Assign To</label>
                        <select name="assigned_to" id="assigned_to"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            <option value="">Not Assigned</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ $request->assigned_to == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Costs --}}
                    <div>
                        <label for="costs" class="block text-sm font-semibold text-gray-700 mb-2">Costs ($)</label>
                        <input type="number" name="costs" id="costs" step="0.01" min="0" value="{{ old('costs', $request->costs) }}"
                            placeholder="0.00"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        @error('costs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- How We Fixed It --}}
                    <div>
                        <label for="how_we_fixed_it" class="block text-sm font-semibold text-gray-700 mb-2">How We Fixed It</label>
                        <textarea name="how_we_fixed_it" id="how_we_fixed_it" rows="4"
                            placeholder="Describe the resolution..."
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">{{ old('how_we_fixed_it', $request->how_we_fixed_it) }}</textarea>
                        @error('how_we_fixed_it')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Lightbox Modal --}}
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center p-4" onclick="closeLightbox(event)">
    <div class="relative max-w-7xl max-h-full">
        <button onclick="closeLightbox(event)" class="absolute -top-12 right-0 text-white hover:text-gray-300 text-4xl font-bold">
            &times;
        </button>
        <img id="lightbox-image" src="" alt="Enlarged view" class="max-w-full max-h-[90vh] object-contain">
    </div>
</div>

<script>
// Lightbox functionality
document.addEventListener('DOMContentLoaded', function() {
    const lightboxTriggers = document.querySelectorAll('.lightbox-trigger');
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    
    lightboxTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const imageUrl = this.getAttribute('data-image');
            lightboxImage.src = imageUrl;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
        });
    });
    
    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !lightbox.classList.contains('hidden')) {
            closeLightbox(e);
        }
    });
});

function closeLightbox(event) {
    // Only close if clicking on the backdrop or close button, not the image itself
    if (event.target.id === 'lightbox' || event.target.tagName === 'BUTTON') {
        const lightbox = document.getElementById('lightbox');
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
    }
}
</script>
@endsection
