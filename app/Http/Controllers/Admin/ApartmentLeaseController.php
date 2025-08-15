<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApartmentLease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApartmentLeaseController extends Controller
{


    /**
     * Display a listing of apartment leases.
     */
    public function index(Request $request)
    {
        $query = ApartmentLease::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('apartment_address', 'like', "%{$search}%")
                    ->orWhere('lease_holder', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Family filter
        if ($request->filled('family_filter') && $request->family_filter !== 'all') {
            if ($request->family_filter === 'yes') {
                $query->whereIn('is_family', ['Yes', 'yes']);
            } elseif ($request->family_filter === 'no') {
                $query->whereIn('is_family', ['No', 'no']);
            }
        }

        // Car filter
        if ($request->filled('car_filter') && $request->car_filter !== 'all') {
            if ($request->car_filter === 'with_car') {
                $query->where('has_car', '>', 0);
            } elseif ($request->car_filter === 'no_car') {
                $query->where('has_car', '=', 0);
            }
        }

        // Get paginated results
        $leases = $query->orderBy('store_number')->paginate(15)->withQueryString();

        // Calculate statistics
        $stats = $this->calculateStats();

        return view('admin.apartment-leases.index', compact('leases', 'stats'));
    }

    /**
     * Show the form for creating a new apartment lease.
     */
    public function create()
    {
        return view('admin.apartment-leases.create');
    }

    /**
     * Store a newly created apartment lease in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_number' => 'nullable|integer',
            'apartment_address' => 'required|string',
            'rent' => 'required|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'number_of_AT' => 'required|integer|min:1',
            'has_car' => 'required|integer|min:0',
            'is_family' => 'nullable|in:Yes,No,yes,no',
            'expiration_date' => 'nullable|date',
            'drive_time' => 'nullable|string',
            'notes' => 'nullable|string',
            'lease_holder' => 'required|string'
        ]);

        // Add the current user's ID to the validated data
        $validated['created_by'] = auth()->id();

        ApartmentLease::create($validated);

        return redirect()->route('admin.apartment-leases.index')
            ->with('success', 'Apartment lease created successfully.');
    }

    /**
     * Display the specified apartment lease.
     */
    public function show(ApartmentLease $apartmentLease)
    {
        return view('admin.apartment-leases.show', compact('apartmentLease'));
    }

    /**
     * Show the form for editing the specified apartment lease.
     */
    public function edit(ApartmentLease $apartmentLease)
    {
        return view('admin.apartment-leases.edit', compact('apartmentLease'));
    }

    /**
     * Update the specified apartment lease in storage.
     */
    public function update(Request $request, ApartmentLease $apartmentLease)
    {
        $validated = $request->validate([
            'store_number' => 'nullable|numeric',
            'apartment_address' => 'required|string',
            'rent' => 'required|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'number_of_AT' => 'required|integer|min:1',
            'has_car' => 'required|integer|min:0',
            'is_family' => 'nullable|in:Yes,No,yes,no',
            'expiration_date' => 'nullable|date',
            'drive_time' => 'nullable|string',
            'notes' => 'nullable|string',
            'lease_holder' => 'required|string'
        ]);

        $apartmentLease->update($validated);

        return redirect()->route('admin.apartment-leases.index')
            ->with('success', 'Apartment lease updated successfully.');
    }

    /**
     * Remove the specified apartment lease from storage.
     */
    public function destroy(ApartmentLease $apartmentLease)
    {
        $apartmentLease->delete();

        return redirect()->route('admin.apartment-leases.index')
            ->with('success', 'Apartment lease deleted successfully.');
    }

    /**
     * Export leases as CSV.
     */
    public function export(Request $request)
    {
        $query = ApartmentLease::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('apartment_address', 'like', "%{$search}%")
                    ->orWhere('lease_holder', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('family_filter') && $request->family_filter !== 'all') {
            if ($request->family_filter === 'yes') {
                $query->whereIn('is_family', ['Yes', 'yes']);
            } elseif ($request->family_filter === 'no') {
                $query->whereIn('is_family', ['No', 'no']);
            }
        }

        if ($request->filled('car_filter') && $request->car_filter !== 'all') {
            if ($request->car_filter === 'with_car') {
                $query->where('has_car', '>', 0);
            } elseif ($request->car_filter === 'no_car') {
                $query->where('has_car', '=', 0);
            }
        }

        $leases = $query->orderBy('store_number')->get();

        $filename = 'apartment-leases-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($leases) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Store Number', 'Apartment Address', 'Rent', 'Utilities', 'Total Rent', 'Number of AT', 'Has Car', 'Is Family', 'Expiration Date', 'Drive Time', 'Notes', 'Lease Holder', 'Expiration Warning'
            ]);

            foreach ($leases as $lease) {
                fputcsv($file, [
                    $lease->store_number,
                    $lease->apartment_address,
                    $lease->rent,
                    $lease->utilities,
                    $lease->total_rent,
                    $lease->number_of_AT,
                    $lease->has_car,
                    $lease->is_family,
                    $lease->expiration_date ? $lease->expiration_date->format('Y-m-d') : '',
                    $lease->drive_time,
                    $lease->notes,
                    $lease->lease_holder,
                    $lease->expiration_warning
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }



    /**
     * Calculate statistics for the index page.
     */
    private function calculateStats()
    {
        $total = ApartmentLease::count();
        $families = ApartmentLease::whereIn('is_family', ['Yes', 'yes'])->count();
        $totalCars = ApartmentLease::sum('has_car');
        $expiringSoon = ApartmentLease::whereBetween('expiration_date', [now(), now()->addMonth()])->count();
        $totalMonthlyRent = ApartmentLease::sum(DB::raw('rent + COALESCE(utilities, 0)'));
        $averageRent = ApartmentLease::avg(DB::raw('rent + COALESCE(utilities, 0)'));
        $averageAT = ApartmentLease::avg('number_of_AT');
        $totalAT = ApartmentLease::sum('number_of_AT');
        $occupancyRate = $total ? 100 : 0;

        return [
            'total' => $total,
            'families' => $families,
            'total_cars' => $totalCars,
            'expiring_soon' => $expiringSoon,
            'total_monthly_rent' => $totalMonthlyRent,
            'average_rent' => $averageRent,
            'average_at' => $averageAT,
            'total_at' => $totalAT,
            'occupancy_rate' => $occupancyRate,
            'expiring_this_month' => ApartmentLease::whereMonth('expiration_date', now()->month)->whereYear('expiration_date', now()->year)->count(),
            'expiring_next_month' => ApartmentLease::whereMonth('expiration_date', now()->addMonth()->month)->whereYear('expiration_date', now()->addMonth()->year)->count(),
            'expiring_next_3_months' => ApartmentLease::whereBetween('expiration_date', [now(), now()->addMonths(3)])->count(),
        ];
    }
    public function list()
    {

        $leases = ApartmentLease::all(); // Fetch all apartment leases; adjust query as needed (e.g., with pagination)
        return view('admin.apartment-leases.list', compact('leases'));
    }
}
