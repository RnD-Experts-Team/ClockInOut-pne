@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Date Filter Form & Export Button -->
    <form method="GET" action="{{ route('admin.clocking') }}" id="filter-form" class="mb-4 row g-3">
        <div class="col-md-4">
            <label for="start_date" class="form-label">Start Date:</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Export Button -->
    <form method="GET" action="{{ route('admin.clocking.export') }}">
        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        <button type="submit" class="btn btn-success mb-3">Export CSV</button>
    </form>

    <!-- Clocking Records Table -->
    @if(isset($clockings) && $clockings->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Miles In</th>
                        <th>Miles Out</th>
                        <th>Image In</th>
                        <th>Image Out</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clockings as $clocking)
                        <tr>
                            <td>{{ $clocking->user->name ?? 'N/A' }}</td>
                            <td>{{ $clocking->clock_in ? $clocking->clock_in : '-' }}</td>
                            <td>{{ $clocking->clock_out ? $clocking->clock_out : '-' }}</td>
                            <td>{{ $clocking->miles_in ?? '-' }}</td>
                            <td>{{ $clocking->miles_out ?? '-' }}</td>
                            <td>
                                @if(!empty($clocking->image_in))
                                    <a href="{{ asset('storage/' . $clocking->image_in) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $clocking->image_in) }}" alt="Clock In Image" width="50">
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!empty($clocking->image_out))
                                    <a href="{{ asset('storage/' . $clocking->image_out) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $clocking->image_out) }}" alt="Clock Out Image" width="50">
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-center text-gray-500">No clocking records found.</p>
    @endif
</div>

<!-- JavaScript for Auto-Submit Filtering -->
<script>
    document.getElementById('start_date').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });

    document.getElementById('end_date').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
</script>

@endsection
