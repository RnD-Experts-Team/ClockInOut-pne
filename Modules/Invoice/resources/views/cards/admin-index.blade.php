@extends('layouts.app')

@section('title', 'Invoice Cards - Ready to Generate')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Invoice Cards</h1>
                <p class="mt-2 text-sm text-black-600">Completed cards ready to generate invoices</p>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        These are completed invoice cards from technicians. Click "Generate Invoice" to create a final invoice for the store.
                    </p>
                </div>
            </div>
        </div>

        <!-- Cards Table -->
        <div class="bg-orange-50 shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg overflow-hidden">
            @if($invoiceCards->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-orange-200">
                        <thead class="bg-orange-100">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Card ID</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Technician</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Store</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Hours</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Costs</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Total</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-200 bg-orange-50">
                        @foreach($invoiceCards as $card)
                            <tr class="hover:bg-orange-100 transition-colors duration-150">
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-bold text-black-900">#{{ $card->id }}</div>
                                    <div class="text-xs text-black-600">{{ $card->created_at->format('m/d/Y') }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-medium text-black-900">{{ $card->user->name }}</div>
                                    <div class="text-xs text-black-600">{{ $card->user->email }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-medium text-black-900">Store #{{ $card->store->store_number }}</div>
                                    <div class="text-xs text-black-600">{{ $card->store->name }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-black-900">
                                    <div>{{ $card->start_time->format('m/d/Y') }}</div>
                                    <div class="text-xs text-black-600">
                                        {{ $card->start_time->format('g:i A') }} - {{ $card->end_time ? $card->end_time->format('g:i A') : 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-black-900">
                                    {{ number_format(($card->labor_hours ?? 0) + ($card->accumulated_labor_hours ?? 0), 2) }} hrs
                                    @if($card->accumulated_labor_hours > 0)
                                        <span class="text-xs text-black-500">({{ number_format($card->accumulated_labor_hours, 2) }} + {{ number_format($card->labor_hours ?? 0, 2) }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="space-y-1 text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="text-blue-600">⏱</span>
                                            <span>Labor: ${{ number_format($card->labor_cost ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-green-600">📋</span>
                                            <span>Materials: ${{ number_format($card->materials_cost ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-orange-600">🚗</span>
                                            <span>Mileage: ${{ number_format($card->mileage_payment ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-bold text-orange-600 text-lg">${{ number_format($card->total_cost ?? 0, 2) }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('invoice.cards.show', $card->id) }}" 
                                           class="text-blue-600 hover:text-blue-700 font-medium" 
                                           title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('invoice.invoices.generate-from-card') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="card_id" value="{{ $card->id }}">
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-700 font-medium" 
                                                    title="Generate Invoice"
                                                    onclick="return confirm('Generate invoice from this card?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-orange-200 sm:px-6">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-black-700">
                            Showing {{ $invoiceCards->firstItem() }} to {{ $invoiceCards->lastItem() }} of {{ $invoiceCards->total() }} results
                        </div>
                        <div class="flex space-x-1">
                            {{ $invoiceCards->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-black-900">No completed cards found</h3>
                    <p class="mt-1 text-sm text-black-700">All completed cards have been converted to invoices, or there are no completed cards yet.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
