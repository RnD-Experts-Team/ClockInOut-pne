@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100 py-8">
    <div class="mx-auto max-w-4xl px-4">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-blue-900 mb-2">Clocking Integration Utilities</h1>
            <p class="text-blue-700">Manage synchronization between Invoice Cards and Clocking records</p>
        </div>

        <!-- Sync All Records Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg ring-1 ring-blue-900/5 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Sync All Clocking Records</h2>
                    <p class="text-gray-600">Update all clocking records with their associated material costs</p>
                </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex items-start gap-2">
                    <svg class="h-5 w-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-yellow-800 font-medium">Important</p>
                        <p class="text-yellow-700 text-sm mt-1">This will update all clocking records to match their associated invoice card materials. Use this after migrating data or if sync gets out of sync.</p>
                    </div>
                </div>
            </div>
            
            <button id="syncAllButton" onclick="syncAllRecords()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center gap-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span id="syncButtonText">Sync All Records</span>
            </button>
            
            <div id="syncResult" class="hidden mt-4"></div>
        </div>

        <!-- Statistics Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg ring-1 ring-blue-900/5">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">System Statistics</h2>
                    <p class="text-gray-600">Current state of the integration</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $totalCards = \Modules\Invoice\Models\InvoiceCard::count();
                    $cardsWithMaterials = \Modules\Invoice\Models\InvoiceCard::whereHas('materials')->count();
                    $totalMaterials = \Modules\Invoice\Models\InvoiceCardMaterial::count();
                    $clockingWithPurchases = \App\Models\Clocking::where('bought_something', true)->count();
                @endphp
                
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-2xl font-bold text-blue-900">{{ $totalCards }}</div>
                    <div class="text-blue-700 text-sm">Total Invoice Cards</div>
                </div>
                
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-2xl font-bold text-green-900">{{ $cardsWithMaterials }}</div>
                    <div class="text-green-700 text-sm">Cards with Materials</div>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="text-2xl font-bold text-purple-900">{{ $totalMaterials }}</div>
                    <div class="text-purple-700 text-sm">Total Materials</div>
                </div>
                
                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="text-2xl font-bold text-orange-900">{{ $clockingWithPurchases }}</div>
                    <div class="text-orange-700 text-sm">Clocking with Purchases</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function syncAllRecords() {
    const button = document.getElementById('syncAllButton');
    const buttonText = document.getElementById('syncButtonText');
    const resultDiv = document.getElementById('syncResult');
    
    // Disable button and show loading
    button.disabled = true;
    buttonText.textContent = 'Syncing...';
    button.classList.add('opacity-50');
    
    try {
        const response = await fetch('{{ route("invoice.cards.sync-clocking-records") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-300 rounded-lg';
            resultDiv.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-green-800 font-medium">Success!</span>
                </div>
                <p class="text-green-700 mt-1">${data.message}</p>
            `;
        } else {
            throw new Error(data.message || 'Sync failed');
        }
        
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-300 rounded-lg';
        resultDiv.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-800 font-medium">Error!</span>
            </div>
            <p class="text-red-700 mt-1">${error.message}</p>
        `;
    } finally {
        // Re-enable button
        button.disabled = false;
        buttonText.textContent = 'Sync All Records';
        button.classList.remove('opacity-50');
        resultDiv.classList.remove('hidden');
        
        // Auto-hide result after 10 seconds
        setTimeout(() => {
            resultDiv.classList.add('hidden');
        }, 10000);
    }
}
</script>
@endsection