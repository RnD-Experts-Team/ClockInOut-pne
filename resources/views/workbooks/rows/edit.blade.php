{{-- resources/views/workbooks/rows/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Row · ' . $workbook->name)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-black-600">
            <li>
                <a href="{{ route('workbooks.folders.index') }}" class="hover:text-orange-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </a>
            </li>
            <li>
                <svg class="w-4 h-4 text-black-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </li>
            <li>
                <a href="{{ route('workbooks.folders.show', $workbook->folder) }}" class="hover:text-orange-600 transition-colors">
                    {{ $workbook->folder->name }}
                </a>
            </li>
            <li>
                <svg class="w-4 h-4 text-black-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </li>
            <li>
                <a href="{{ route('workbooks.show', [$workbook->folder, $workbook]) }}" class="hover:text-orange-600 transition-colors">
                    {{ $workbook->name }}
                </a>
            </li>
            <li>
                <svg class="w-4 h-4 text-black-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </li>
            <li class="font-semibold text-black-900">Edit Row</li>
        </ol>
    </nav>

    <!-- Panel -->
    <div class="bg-orange-50 shadow-lg rounded-2xl border border-orange-200 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-orange-200 bg-gradient-to-r from-orange-100 to-amber-100">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold text-black-900 flex items-center">
                    <span class="p-2 mr-3 bg-orange-600 rounded-lg inline-flex">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    Edit Row
                </h1>
                <a href="{{ route('workbooks.show', [$workbook->folder, $workbook]) }}"
                   class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-xl text-black-700 
                          bg-white border-2 border-orange-300 hover:bg-orange-100 hover:border-orange-400">
                    Back to Data
                </a>
            </div>
        </div>

        <!-- Subheader -->
        <div class="px-6 py-3 bg-orange-75 border-b border-orange-200 text-xs text-black-600">
            Last updated: {{ optional($row->updated_at)->diffForHumans() ?? '—' }}
        </div>

        <!-- Form -->
        <form action="{{ route('workbooks.rows.update', [$workbook->folder, $workbook, $row]) }}" method="POST" class="p-6 space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($workbook->columns as $index => $column)
                    @php $value = $row->getCellValue($column->id); @endphp
                    <div class="space-y-2 group">
                        <div class="flex items-center justify-between">
                            <label for="cell_{{ $column->id }}" class="block text-sm font-semibold text-black-700 flex items-center space-x-2">
                                <span>{{ $column->name }}</span>
                                @if($loop->first)
                                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full">Required</span>
                                @endif
                            </label>
                            <span class="text-xs text-black-400 group-hover:text-black-600 transition-colors">
                                {{ $loop->iteration }}/{{ $loop->count }}
                            </span>
                        </div>

                        <div class="relative">
                            <input type="text"
                                   id="cell_{{ $column->id }}"
                                   name="cells[{{ $column->id }}]"
                                   value="{{ old('cells.'.$column->id, $value) }}"
                                   class="block w-full rounded-xl border-2 border-orange-200 shadow-sm 
                                          focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 
                                          sm:text-sm py-3 px-4 transition-all duration-200
                                          hover:border-orange-300 bg-white {{ $loop->first ? 'required-field' : '' }}"
                                   placeholder="Enter {{ strtolower($column->name) }}..."
                                   autocomplete="off">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <span class="text-xs text-black-400 char-counter" style="{{ strlen($value ?? '') ? '' : 'display:none;' }}">{{ strlen($value ?? '') }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-black-500 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            Edit data for {{ strtolower($column->name) }} column
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-orange-200 pt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-2 text-sm text-black-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Changes apply immediately after saving</span>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('workbooks.show', [$workbook->folder, $workbook]) }}"
                           class="px-6 py-3 text-sm font-medium rounded-xl text-black-700 
                                  bg-white border-2 border-orange-300 hover:bg-orange-100 hover:border-orange-400">
                            Discard
                        </a>
                        <button type="submit"
                                class="px-6 py-3 text-sm font-medium rounded-xl text-white 
                                       bg-gradient-to-r from-orange-600 to-amber-600 
                                       hover:from-orange-700 hover:to-amber-700 shadow-lg hover:shadow-xl
                                       transform hover:scale-105 transition-all duration-200">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
  .scrollbar-thin::-webkit-scrollbar { width: 6px; }
  .scrollbar-thin::-webkit-scrollbar-track { background: #fed7aa; border-radius: 3px; }
  .scrollbar-thin::-webkit-scrollbar-thumb { background: #fb923c; border-radius: 3px; }
  .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #ea580c; }
  .required-field:invalid { border-color: #ef4444; }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('input', (e) => {
    if (!e.target.matches('input[type="text"]')) return;
    const counter = e.target.parentNode.querySelector('.char-counter');
    if (!counter) return;
    const len = e.target.value.length;
    counter.textContent = len;
    counter.style.display = len ? 'block' : 'none';
    counter.classList.toggle('text-orange-600', len > 100);
    counter.classList.toggle('font-semibold', len > 100);
  });
</script>
@endpush
