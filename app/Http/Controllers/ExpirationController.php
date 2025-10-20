<?php

namespace App\Http\Controllers;

use App\Models\ExpirationTracking;
use App\Services\ExpirationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ExpirationController extends Controller
{
    protected ExpirationService $expirationService;

    public function __construct(ExpirationService $expirationService)
    {
        $this->expirationService = $expirationService;
    }

    /**
     * Expiration tracking overview
     */
    public function index(Request $request): View
    {
        $filterType = $request->get('type', 'all');
        $filterStatus = $request->get('status', 'active');

        $query = ExpirationTracking::with('trackableModel');

        if ($filterType !== 'all') {
            $query->byType($filterType);
        }

        if ($filterStatus !== 'all') {
            $query->where('status', $filterStatus);
        }

        $expirations = $query->orderBy('expiration_date', 'asc')->paginate(20);

        $expiringSoon = ExpirationTracking::expiringSoon(30)->active()->count();
        $expired = ExpirationTracking::expired()->count();

        $expirationTypes = ExpirationTracking::distinct('expiration_type')->pluck('expiration_type');

        return view('expiration.index', compact(
            'expirations',
            'expiringSoon',
            'expired',
            'expirationTypes',
            'filterType',
            'filterStatus'
        ));
    }

    /**
     * Track new model for expiration
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'expiration_date' => 'required|date|after:today',
            'expiration_type' => 'required|in:lease_end,officer_term,department_closure,contract_end,license_expiry',
            'warning_days' => 'integer|min:1|max:365',
            'notes' => 'nullable|string',
        ]);

        $expiration = ExpirationTracking::create($validated);

        // Create calendar event
        $expiration->createReminderEvent();

        return response()->json([
            'success' => true,
            'message' => 'Expiration tracking added successfully',
            'expiration' => $expiration
        ]);
    }

    /**
     * Show expiration details
     */
    public function show(ExpirationTracking $expiration): View
    {
        $expiration->load('trackableModel');
        $daysUntilExpiration = $expiration->daysUntilExpiration();

        return view('expiration.show', compact('expiration', 'daysUntilExpiration'));
    }

    /**
     * Update expiration tracking
     */
    public function update(Request $request, ExpirationTracking $expiration): JsonResponse
    {
        $validated = $request->validate([
            'expiration_date' => 'required|date',
            'warning_days' => 'integer|min:1|max:365',
            'notes' => 'nullable|string',
        ]);

        $expiration->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Expiration tracking updated successfully'
        ]);
    }

    /**
     * Renew/extend expiration
     */
    public function renew(Request $request, ExpirationTracking $expiration): JsonResponse
    {
        $validated = $request->validate([
            'new_expiration_date' => 'required|date|after:today',
            'action' => 'required|in:renew,extend',
            'notes' => 'nullable|string',
        ]);

        if ($validated['action'] === 'renew') {
            $expiration->renew(Carbon::parse($validated['new_expiration_date']), $validated['notes']);
        } else {
            $days = Carbon::today()->diffInDays(Carbon::parse($validated['new_expiration_date']));
            $expiration->extend($days, $validated['notes']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expiration ' . $validated['action'] . 'd successfully'
        ]);
    }

    /**
     * Get expiring items (AJAX)
     */
    public function getExpiringItems(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);

        $expiringSoon = ExpirationTracking::expiringSoon($days)
            ->with('trackableModel')
            ->get();

        $expired = ExpirationTracking::expired()
            ->with('trackableModel')
            ->get();

        return response()->json([
            'expiring_soon' => $expiringSoon->map(function ($item) {
                return [
                    'id' => $item->id,
                    'model_type' => class_basename($item->model_type),
                    'model_id' => $item->model_id,
                    'expiration_date' => $item->expiration_date->format('Y-m-d'),
                    'days_until_expiration' => $item->daysUntilExpiration(),
                    'expiration_type' => $item->expiration_type,
                    'status' => $item->status,
                ];
            }),
            'expired' => $expired->map(function ($item) {
                return [
                    'id' => $item->id,
                    'model_type' => class_basename($item->model_type),
                    'model_id' => $item->model_id,
                    'expiration_date' => $item->expiration_date->format('Y-m-d'),
                    'days_since_expiration' => abs($item->daysUntilExpiration()),
                    'expiration_type' => $item->expiration_type,
                    'status' => $item->status,
                ];
            }),
            'summary' => [
                'total_expiring_soon' => $expiringSoon->count(),
                'total_expired' => $expired->count(),
                'by_type' => $expiringSoon->concat($expired)->groupBy('expiration_type')->map->count(),
            ]
        ]);
    }

    /**
     * Update warning settings
     */
    public function updateWarningSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expiration_ids' => 'required|array',
            'expiration_ids.*' => 'exists:expiration_trackings,id',
            'warning_days' => 'required|integer|min:1|max:365',
        ]);

        ExpirationTracking::whereIn('id', $validated['expiration_ids'])
            ->update(['warning_days' => $validated['warning_days']]);

        return response()->json([
            'success' => true,
            'message' => 'Warning settings updated for ' . count($validated['expiration_ids']) . ' items'
        ]);
    }

    /**
     * Delete expiration tracking
     */
    public function destroy(ExpirationTracking $expiration): JsonResponse
    {
        $expiration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expiration tracking removed successfully'
        ]);
    }
}
