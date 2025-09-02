<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ApartmentLeasesImport;
use App\Models\ApartmentLease;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ApartmentLeaseController extends Controller
{
    public function index(Request $request)
    {
        $query = ApartmentLease::with('store');

        // Create a base query for stats calculation
        $baseQuery = clone $query;

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFilter = function ($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('apartment_address', 'like', "%{$search}%")
                    ->orWhere('lease_holder', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('store', function($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            };

            $query->where($searchFilter);
            $baseQuery->where($searchFilter);
        }

        // Family filter
        if ($request->filled('family_filter') && $request->family_filter !== 'all') {
            $familyFilter = function ($q) use ($request) {
                if ($request->family_filter === 'yes') {
                    $q->whereIn('is_family', ['Yes', 'yes']);
                } elseif ($request->family_filter === 'no') {
                    $q->whereIn('is_family', ['No', 'no']);
                }
            };

            $query->where($familyFilter);
            $baseQuery->where($familyFilter);
        }

        // Car filter
        if ($request->filled('car_filter') && $request->car_filter !== 'all') {
            $carFilter = function ($q) use ($request) {
                if ($request->car_filter === 'with_car') {
                    $q->where('has_car', '>', 0);
                } elseif ($request->car_filter === 'no_car') {
                    $q->where('has_car', '=', 0);
                }
            };

            $query->where($carFilter);
            $baseQuery->where($carFilter);
        }

        // Store filter
        if ($request->filled('store_id') && $request->store_id !== 'all') {
            $storeFilter = function ($q) use ($request) {
                $q->where('store_id', $request->store_id);
            };

            $query->where($storeFilter);
            $baseQuery->where($storeFilter);
        }

        // Date range filter for expiration dates
        if ($request->has('date_range') && $request->date_range !== 'all') {
            $dateFilter = function($q) use ($request) {
                switch ($request->date_range) {
                    case 'expiring_this_month':
                        $q->whereMonth('expiration_date', Carbon::now()->month)
                            ->whereYear('expiration_date', Carbon::now()->year);
                        break;
                    case 'expiring_next_month':
                        $nextMonth = Carbon::now()->addMonth();
                        $q->whereMonth('expiration_date', $nextMonth->month)
                            ->whereYear('expiration_date', $nextMonth->year);
                        break;
                    case 'expiring_3_months':
                        $q->whereBetween('expiration_date', [
                            Carbon::now()->startOfDay(),
                            Carbon::now()->addMonths(3)->endOfDay()
                        ]);
                        break;
                    case 'expired':
                        $q->where('expiration_date', '<', Carbon::now()->startOfDay());
                        break;
                    case 'custom':
                        if ($request->has('start_date') && $request->has('end_date')) {
                            $q->whereBetween('expiration_date', [
                                Carbon::parse($request->start_date)->startOfDay(),
                                Carbon::parse($request->end_date)->endOfDay()
                            ]);
                        }
                        break;
                }
            };

            $query->where($dateFilter);
            $baseQuery->where($dateFilter);
        }

        $leases = $query->orderBy('store_number')->paginate(15)->withQueryString();

        // Calculate filtered stats using baseQuery
        $stats = $this->calculateFilteredStats($baseQuery);

        $stores = Store::orderBy('store_number')->get();

        return view('admin.apartment-leases.index', compact('leases', 'stats', 'stores'));
    }
    private function calculateFilteredStats($query)
    {
        $total = (clone $query)->count();
        $totalMonthlyRent = (clone $query)->sum(DB::raw('rent + COALESCE(utilities, 0)'));
        $families = (clone $query)->whereIn('is_family', ['Yes', 'yes'])->count();
        $totalCars = (clone $query)->sum('has_car');
        $totalAT = (clone $query)->sum('number_of_AT');

        // Fix the date range calculation
        $startDate = now()->startOfDay();
        $endDate = now()->addMonth()->endOfMonth(); // End of next month instead of exact 1 month

        $expiringSoon = (clone $query)->whereBetween('expiration_date', [$startDate, $endDate])->count();


        return [
            'total' => $total,
            'families' => $families,
            'total_cars' => $totalCars,
            'expiring_soon' => $expiringSoon,
            'total_monthly_rent' => $totalMonthlyRent,
            'average_rent' => $total > 0 ? $totalMonthlyRent / $total : 0,
            'total_at' => $totalAT,
            'average_at' => $total > 0 ? $totalAT / $total : 0,
            'occupancy_rate' => $total > 0 ? 100 : 0,
            'expiring_this_month' => $query->whereMonth('expiration_date', now()->month)
                ->whereYear('expiration_date', now()->year)->count(),
            'expiring_next_month' => $query->whereMonth('expiration_date', now()->addMonth()->month)
                ->whereYear('expiration_date', now()->addMonth()->year)->count(),
            'expiring_next_3_months' => $query->whereBetween('expiration_date', [now(), now()->addMonths(3)])->count(),
        ];
    }

    public function create()
    {
        $stores = Store::orderBy('store_number')->get();
        return view('admin.apartment-leases.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
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

        DB::beginTransaction();
        try {
            // Handle store creation if needed
            if (!$validated['store_id'] && $validated['new_store_number']) {
                $store = Store::create([
                    'store_number' => $validated['new_store_number'],
                    'name' => $validated['new_store_name'],
                    'is_active' => true,
                ]);
                $validated['store_id'] = $store->id;
                $validated['store_number'] = $store->store_number;
            } elseif ($validated['store_id']) {
                $store = Store::find($validated['store_id']);
                $validated['store_number'] = $store->store_number;
            }

            $validated['created_by'] = auth()->id();
            unset($validated['new_store_number'], $validated['new_store_name']);

            ApartmentLease::create($validated);

            DB::commit();

            return redirect()->route('admin.apartment-leases.index')
                ->with('success', 'Apartment lease created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create apartment lease: ' . $e->getMessage()]);
        }
    }

    public function show(ApartmentLease $apartmentLease)
    {
        $apartmentLease->load('store');
        return view('admin.apartment-leases.show', compact('apartmentLease'));
    }

    public function edit(ApartmentLease $apartmentLease)
    {
        $stores = Store::orderBy('store_number')->get();
        return view('admin.apartment-leases.edit', compact('apartmentLease', 'stores'));
    }

    public function update(Request $request, ApartmentLease $apartmentLease)
    {
        $validated = $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
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

        DB::beginTransaction();
        try {
            // Handle store creation if needed
            if (!$validated['store_id'] && $validated['new_store_number']) {
                $store = Store::create([
                    'store_number' => $validated['new_store_number'],
                    'name' => $validated['new_store_name'],
                    'is_active' => true,
                ]);
                $validated['store_id'] = $store->id;
                $validated['store_number'] = $store->store_number;
            } elseif ($validated['store_id']) {
                $store = Store::find($validated['store_id']);
                $validated['store_number'] = $store->store_number;
            }

            unset($validated['new_store_number'], $validated['new_store_name']);

            $apartmentLease->update($validated);

            DB::commit();

            return redirect()->route('admin.apartment-leases.index')
                ->with('success', 'Apartment lease updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update apartment lease: ' . $e->getMessage()]);
        }
    }

    public function destroy(ApartmentLease $apartmentLease)
    {
        $apartmentLease->delete();

        return redirect()->route('admin.apartment-leases.index')
            ->with('success', 'Apartment lease deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = ApartmentLease::with('store');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('apartment_address', 'like', "%{$search}%")
                    ->orWhere('lease_holder', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('store', function($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
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
                'Store Number', 'Store Name', 'Apartment Address', 'Rent', 'Utilities', 'Total Rent', 'Number of AT', 'Has Car', 'Is Family', 'Expiration Date', 'Drive Time', 'Notes', 'Lease Holder', 'Expiration Warning'
            ]);

            foreach ($leases as $lease) {
                fputcsv($file, [
                    $lease->store ? $lease->store->store_number : $lease->store_number,
                    $lease->store ? $lease->store->name : 'N/A',
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
        $leases = ApartmentLease::with('store')->get();
        return view('admin.apartment-leases.list', compact('leases'));
    }

    public function importXlsx(Request $request)
    {
        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new ApartmentLeasesImport, $request->file('xlsx_file'));

            return redirect()->route('admin.apartment-leases.index')
                ->with('success', 'Excel file imported successfully!');

        } catch (\Exception $e) {
            return redirect()->route('admin.apartment-leases.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
