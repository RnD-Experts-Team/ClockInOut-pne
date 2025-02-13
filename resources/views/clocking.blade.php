@extends('layouts.app')

@section('title', 'Clocking')

@section('content')
    <h2 class="text-3xl font-bold text-center mb-8">
        @if ($clocking)
            Clock Out
        @else
            Clock In
        @endif
    </h2>

    @if ($clocking)
        <!-- Clock Out Form -->
        <form action="{{ route('clocking.clockOut') }}" method="POST" enctype="multipart/form-data" class="max-w-md mx-auto space-y-6">
            @csrf
            <div>
                <label for="miles_out" class="block text-sm font-medium text-gray-700 mb-1">Miles Out</label>
                <input type="number" id="miles_out" name="miles_out" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter miles" required>
            </div>
            <div>
                <label for="image_out" class="block text-sm font-medium text-gray-700 mb-1">Upload Image</label>
                <input type="file" id="image_out" name="image_out" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-red-500 text-white font-bold py-3 px-4 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                Clock Out
            </button>
        </form>
    @else
        <!-- Clock In Form -->
        <form action="{{ route('clocking.clockIn') }}" method="POST" enctype="multipart/form-data" class="max-w-md mx-auto space-y-6">
            @csrf
            <div>
                <label for="miles_in" class="block text-sm font-medium text-gray-700 mb-1">Miles In</label>
                <input type="number" id="miles_in" name="miles_in" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter miles" required>
            </div>
            <div>
                <label for="image_in" class="block text-sm font-medium text-gray-700 mb-1">Upload Image</label>
                <input type="file" id="image_in" name="image_in" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-green-500 text-white font-bold py-3 px-4 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                Clock In
            </button>
        </form>
    @endif
@endsection
