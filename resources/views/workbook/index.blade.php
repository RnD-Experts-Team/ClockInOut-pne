@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-orange-50 min-h-screen">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-orange-100 to-amber-100 rounded-xl shadow-sm p-6 mb-8 border border-orange-200">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-3xl font-bold text-orange-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Workbook
                    </h1>
                    <p class="mt-3 text-base text-orange-800">
                        Manage a single workbook made of dynamic <strong>columns</strong>, <strong>rows</strong>, and typed <strong>cells</strong>
                        (string, number, date, bool, json). Add columns and rows below, then edit values inline.
                    </p>
                </div>

                {{-- Add Row quick action --}}
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <form method="POST" action="{{ route('workbook.rows.store') }}" class="inline-flex items-center gap-3">
                        @csrf

                        {{-- Row position --}}
                        <div class="relative">
                            <input type="number" name="position" placeholder="Row position"
                                   class="block w-36 rounded-xl border-orange-300 bg-white pl-3 pr-10 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400"/>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>

                        {{-- Store select (optional on create) --}}
                        <div class="relative">
                            <select name="store_id"
                                    class="block w-56 rounded-xl border-orange-300 bg-white pl-3 pr-10 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400">
                                <option value="">(no store)</option>
                                @foreach (($stores ?? collect()) as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->store_number }} @if($s->name) — {{ $s->name }} @endif @if(!$s->is_active) (inactive) @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>

                        <button type="submit"
                                class="group relative inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-br from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 ease-out transform hover:scale-[1.02] active:scale-[0.98] shadow-md hover:shadow-lg shadow-orange-500/30">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-white transform group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v6m-3-3h6M5 12a7 7 0 1014 0A7 7 0 005 12z" />
                                </svg>
                            </span>
                            <span class="pl-8">Add Row</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Flash + errors --}}
        @if (session('status'))
            <div class="mb-6 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-800 px-4 py-3 flex items-center gap-3 shadow-sm">
                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-800 px-4 py-3">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">Please fix the following errors:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 pl-5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Column creator --}}
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6 border border-orange-200 transition-all duration-300 hover:shadow-md">
            <h2 class="text-lg font-semibold text-orange-900 mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New Column
            </h2>
            <form method="POST" action="{{ route('workbook.columns.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 md:gap-6">
                @csrf
                <div class="space-y-2 md:col-span-2">
                    <label class="block text-sm font-medium text-orange-800">Name</label>
                    <input name="name" class="block w-full rounded-lg border-orange-300 bg-orange-50/50 px-3 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400" placeholder="e.g. Price" required>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-orange-800">Type</label>
                    <select name="type" class="block w-full rounded-lg border-orange-300 bg-orange-50/50 px-3 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400">
                        <option value="string">string</option>
                        <option value="number">number</option>
                        <option value="date">date</option>
                        <option value="bool">bool</option>
                        <option value="json">json</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-orange-800">Slug (optional)</label>
                    <input name="slug" class="block w-full rounded-lg border-orange-300 bg-orange-50/50 px-3 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400" placeholder="price">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-orange-800">Position</label>
                    <input type="number" min="0" name="position"
                           class="block w-full rounded-lg border-orange-300 bg-orange-50/50 px-3 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400"
                           placeholder="0">
                </div>
                <div class="md:col-span-5 flex flex-wrap items-center gap-6 pt-2">
                    <label class="inline-flex items-center gap-2 text-sm text-orange-800">
                        <input type="checkbox" name="required" value="1" class="rounded border-orange-400 text-orange-600 focus:ring-orange-500">
                        Required
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-orange-800">
                        <input type="checkbox" name="is_unique" value="1" class="rounded border-orange-400 text-orange-600 focus:ring-orange-500">
                        Unique
                    </label>
                    <button class="ml-auto inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 transform hover:scale-[1.02] shadow-md hover:shadow-lg shadow-orange-500/30">
                        Add Column
                    </button>
                </div>
            </form>
        </div>

        {{-- Grid --}}
        <div class="mt-8 flex flex-col">

            @if (($columns ?? collect())->count() === 0)
                <div class="mt-8 text-center py-12 bg-white rounded-xl border border-orange-200 shadow-sm">
                    <svg class="mx-auto h-16 w-16 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-orange-900">No columns yet</h3>
                    <p class="mt-2 text-sm text-orange-700 max-w-md mx-auto">Create your first column above to start building your workbook. Columns define the structure of your data.</p>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-orange-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-orange-200">
                            <thead class="bg-gradient-to-r from-orange-50 to-amber-50">
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-orange-900 w-28 sticky left-0 bg-orange-100 z-10">
                                    <div class="flex items-center gap-1">
                                        <span>Row</span>
                                        <span class="text-xs text-orange-600 font-normal" title="Total rows">{{ count($rows) }}</span>
                                    </div>
                                </th>

                                {{-- NEW: Store column header --}}
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-orange-900 w-64">
                                    Store
                                </th>

                                @foreach ($columns as $col)
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-orange-900 min-w-[180px]">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-semibold flex items-center gap-1.5">
                                                    <span>{{ $col->name }}</span>
                                                    @if($col->required)
                                                        <span class="text-xs text-red-600 font-medium" title="Required">*</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-orange-600 mt-0.5">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">{{ $col->type }}</span>
                                                    @if($col->slug)
                                                        <span class="ml-1.5">{{ $col->slug }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <form method="POST" action="{{ route('workbook.columns.destroy', $col) }}"
                                                  onsubmit="return confirm('Delete column & all its cell data? This cannot be undone.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex items-center p-1.5 rounded-md bg-white text-orange-600 hover:bg-orange-100 border border-orange-300 shadow-sm hover:text-red-700 transition-colors duration-150" title="Delete column">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </th>
                                @endforeach

                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-orange-900 w-40 sticky right-0 bg-orange-100 z-10">
                                    Actions
                                </th>
                            </tr>
                            </thead>

                            <tbody class="divide-y divide-orange-200 bg-white">
                            @forelse ($rows as $row)
                                @php
                                    $saveFormId = 'save-row-'.$row['id'];
                                    $deleteFormId = 'delete-row-'.$row['id'];
                                @endphp
                                <tr class="hover:bg-orange-50/50 transition-colors duration-150 group">
                                    {{-- Row label --}}
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-orange-900 sticky left-0 bg-white group-hover:bg-orange-50/50 z-10">
                                        <div class="flex flex-col">
                                            <span class="font-semibold">#{{ $row['id'] }}</span>
                                            <span class="text-xs text-orange-600 mt-0.5">pos {{ $row['position'] }}</span>
                                        </div>
                                    </td>

                                    {{-- NEW: Store cell (dropdown bound to save form) --}}
                                    <td class="px-3 py-3 align-top">
                                        <div class="relative">
                                            <select form="{{ $saveFormId }}" name="store_id"
                                                    class="w-full rounded-lg border-orange-300 bg-orange-50/30 px-3 py-2 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400">
                                                <option value="">(no store)</option>
                                                @foreach (($stores ?? collect()) as $s)
                                                    <option value="{{ $s->id }}"
                                                            @selected(old('store_id', $row['store']['id'] ?? null) == $s->id)>
                                                        {{ $s->store_number }} @if($s->name) — {{ $s->name }} @endif @if(!$s->is_active) (inactive) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('store_id')
                                                <div class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </td>

                                    {{-- Editable cells --}}
                                    @foreach ($columns as $col)
                                        @php
                                            $val = $row['cells'][$col->slug] ?? null;
                                            $dt = $val ? \Illuminate\Support\Str::of($val)->replace('Z','') : '';
                                        @endphp
                                        <td class="px-3 py-3 align-top">
                                            <div class="relative">
                                                @if ($col->type === 'number')
                                                    <input form="{{ $saveFormId }}" name="cells[{{ $col->id }}]" type="number" step="any"
                                                           value="{{ old('cells.'.$col->id, $val) }}"
                                                           class="w-full rounded-lg border-orange-300 bg-orange-50/30 px-3 py-2 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400"/>
                                                @elseif ($col->type === 'date')
                                                    <input form="{{ $saveFormId }}" name="cells[{{ $col->id }}]" type="datetime-local"
                                                           value="{{ old('cells.'.$col->id, $dt) }}"
                                                           class="w-full rounded-lg border-orange-300 bg-orange-50/30 px-3 py-2 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400"/>
                                                @elseif ($col->type === 'bool')
                                                    <select form="{{ $saveFormId }}" name="cells[{{ $col->id }}]"
                                                            class="w-full rounded-lg border-orange-300 bg-orange-50/30 px-3 py-2 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400">
                                                        <option value="" @selected(old('cells.'.$col->id, $val) === null)>(null)</option>
                                                        <option value="1" @selected(old('cells.'.$col->id, $val) === true)>true</option>
                                                        <option value="0" @selected(old('cells.'.$col->id, $val) === false)>false</option>
                                                    </select>
                                                @elseif ($col->type === 'json')
                                                    <textarea form="{{ $saveFormId }}" name="cells[{{ $col->id }}]" rows="1" placeholder='{"key":"value"}'
                                                              class="w-full rounded-lg border-orange-300 bg-orange-50/30 px-3 py-2 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400 font-mono text-xs">@php
    $curr = old('cells.'.$col->id);
    if ($curr !== null) {
        echo is_string($curr)
            ? $curr
            : json_encode($curr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo $val
            ? json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : '';
    }
@endphp</textarea>
                                                @else
                                                    {{-- STRING: plain value inside quotes, Blade-escaped (no extra quotes or \uXXXX) --}}
                                                    <input form="{{ $saveFormId }}" name="cells[{{ $col->id }}]" type="text"
                                                           value="{{ old('cells.'.$col->id, $val ?? '') }}"
                                                           class="w-full rounded-lg border-orange-300 bg-orange-50/30 px-3 py-2 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/50 transition-all duration-200 hover:border-orange-400"/>
                                                @endif

                                                {{-- Field-level error --}}
                                                @error('cells.'.$col->id)
                                                    <div class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </td>
                                    @endforeach

                                    {{-- Actions cell with two separate (non-nested) forms --}}
                                    <td class="px-3 py-3 sticky right-0 bg-white group-hover:bg-orange-50/50 z-10">
                                        <div class="flex items-center gap-2">
                                            {{-- SAVE form (empty container; inputs point here via form attr) --}}
                                            <form id="{{ $saveFormId }}" method="POST" action="{{ route('workbook.rows.save', $row['id']) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-150 shadow-sm hover:shadow-md">
                                                    Save
                                                </button>
                                            </form>

                                            {{-- DELETE form (separate sibling) --}}
                                            <form method="POST" action="{{ route('workbook.rows.destroy', $row['id']) }}" class="inline"
                                                  onsubmit="return confirm('Delete this row and all its cell data? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150 shadow-sm hover:shadow-md">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- +3 here because: Row + Store + Actions --}}
                                    <td colspan="{{ ($columns->count() ?: 0) + 3 }}" class="px-3 py-8 text-center text-sm text-orange-700 bg-orange-50/50">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-orange-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                            <p>No rows yet. Use the <strong>Add Row</strong> button to insert your first row.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
