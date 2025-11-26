@extends('layouts.app')

@section('title', 'Request Details')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Request #{{ $request->id }}</h1>
            <p class="mt-1 text-sm text-gray-600">Submitted on {{ $request->request_date->format('F d, Y') }}</p>
        </div>
        <a href="{{ route('native.requests.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            ← Back to My Requests
        </a>
    </div>

    {{-- Status Card --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6 border-l-4 @php
        $borderColors = [
            'pending' => 'border-yellow-500',
            'in_progress' => 'border-blue-500',
            'done' => 'border-green-500',
            'canceled' => 'border-red-500',
        ];
    @endphp {{ $borderColors[$request->status] ?? 'border-gray-500' }}">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Current Status</h3>
                <p class="text-2xl font-bold mt-1" style="color: {{ $request->urgencyLevel->color }};">
                    {{ ucwords(str_replace('_', ' ', $request->status)) }}
                </p>
            </div>
            @if($request->assignedTo)
                <div class="text-right">
                    <p class="text-sm text-gray-600">Assigned to</p>
                    <p class="font-semibold text-gray-900">{{ $request->assignedTo->name }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Request Details Card --}}
    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200 mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Request Details</h2>
        </div>
        <div class="p-6 space-y-4">
            {{-- Store --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="font-semibold text-gray-700">Store:</div>
                <div class="col-span-2 text-gray-900">{{ $request->store->store_number }} - {{ $request->store->name }}</div>
            </div>

            {{-- Equipment --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="font-semibold text-gray-700">Equipment:</div>
                <div class="col-span-2 text-gray-900">{{ $request->equipment_with_issue }}</div>
            </div>

            {{-- Urgency --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="font-semibold text-gray-700">Urgency Level:</div>
                <div class="col-span-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold" 
                        style="background-color: {{ $request->urgencyLevel->color }}20; color: {{ $request->urgencyLevel->color }};">
                        <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $request->urgencyLevel->color }};"></span>
                        {{ $request->urgencyLevel->name }}
                    </span>
                </div>
            </div>

            {{-- Basic Troubleshoot --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="font-semibold text-gray-700">Basic Troubleshooting:</div>
                <div class="col-span-2">
                    @if($request->basic_troubleshoot_done)
                        <span class="inline-flex items-center px-2 py-1 rounded text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Completed
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded text-sm font-medium bg-gray-100 text-gray-800">
                            Not Done
                        </span>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            <div class="pt-4 border-t border-gray-200">
                <div class="font-semibold text-gray-700 mb-2">Description of Issue:</div>
                <div class="text-gray-900 bg-gray-50 rounded-lg p-4">{{ $request->description_of_issue }}</div>
            </div>
        </div>
    </div>

    {{-- Attachments --}}
    @if($request->attachments->count() > 0)
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200 mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Attachments ({{ $request->attachments->count() }})</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($request->attachments as $attachment)
                        <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            @if(Str::startsWith($attachment->mime_type, 'image/'))
                                <a href="{{ $attachment->url }}" target="_blank" class="block">
                                    <img src="{{ $attachment->url }}" alt="{{ $attachment->file_name }}" class="w-full h-32 object-cover">
                                </a>
                            @else
                                <div class="w-full h-32 bg-gray-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                            <div class="p-2 bg-gray-50">
                                <p class="text-xs text-gray-600 truncate">{{ $attachment->file_name }}</p>
                                <p class="text-xs text-gray-500">{{ $attachment->formattedSize }}</p>
                                <a href="{{ $attachment->url }}" download class="text-xs text-orange-600 hover:text-orange-800">Download</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Resolution Info (if completed) --}}
    @if($request->status === 'done' && ($request->costs || $request->how_we_fixed_it))
        <div class="bg-green-50 shadow-xl rounded-xl overflow-hidden border border-green-200">
            <div class="px-6 py-4 bg-green-100 border-b border-green-200">
                <h2 class="text-xl font-bold text-green-900">Resolution Details</h2>
            </div>
            <div class="p-6 space-y-4">
                @if($request->costs)
                    <div class="grid grid-cols-3 gap-4">
                        <div class="font-semibold text-gray-700">Total Costs:</div>
                        <div class="col-span-2 text-gray-900 text-lg font-bold">${{ number_format($request->costs, 2) }}</div>
                    </div>
                @endif

                @if($request->how_we_fixed_it)
                    <div class="pt-4 border-t border-green-200">
                        <div class="font-semibold text-gray-700 mb-2">How We Fixed It:</div>
                        <div class="text-gray-900 bg-white rounded-lg p-4">{{ $request->how_we_fixed_it }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
