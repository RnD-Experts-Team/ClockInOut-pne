<?php

namespace App\Services\Api\Admin;

use App\Models\ExpirationTracking;
use Carbon\Carbon;

class ExpirationService
{
    public function getExpirations($request): array
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

        $expiringSoon = ExpirationTracking::expiringSoon(30)
            ->active()
            ->count();

        $expired = ExpirationTracking::expired()->count();

        $expirationTypes = ExpirationTracking::distinct('expiration_type')
            ->pluck('expiration_type');

        return [
            'expirations' => $expirations,
            'expiring_soon' => $expiringSoon,
            'expired' => $expired,
            'expiration_types' => $expirationTypes,
            'filter_type' => $filterType,
            'filter_status' => $filterStatus,
        ];
    }
    public function storeExpiration(array $validated): array
    {
        $expiration = ExpirationTracking::create($validated);

        // Create calendar event
        $expiration->createReminderEvent();

        return [
            'expiration' => $expiration,
            'message' => 'Expiration tracking added successfully'
        ];
    }
    public function getExpiration($expiration): array
    {
        $expiration->load('trackableModel');

        $daysUntilExpiration = $expiration->daysUntilExpiration();

        return [
            'expiration' => $expiration,
            'days_until_expiration' => $daysUntilExpiration
        ];
    }

    public function updateExpiration(array $validated, $expiration): array
    {
        $expiration->update($validated);

        return [
            'message' => 'Expiration tracking updated successfully',
            'expiration' => $expiration
        ];
    }
    public function deleteExpiration($expiration): array
    {
        $expiration->delete();

        return [
            'message' => 'Expiration tracking removed successfully'
        ];
    }


    public function renewExpiration(array $validated, $expiration): array
    {
        $newDate = Carbon::parse($validated['new_expiration_date']);

        if ($validated['action'] === 'renew') {

            $expiration->renew($newDate, $validated['notes'] ?? null);

        } else {

            $days = Carbon::today()->diffInDays($newDate);

            $expiration->extend($days, $validated['notes'] ?? null);
        }

        return [
            'message' => 'Expiration ' . $validated['action'] . 'd successfully'
        ];
    }
    public function getExpiringItems($days = 30)
    {
        $expiringSoon = ExpirationTracking::expiringSoon($days)
            ->with('trackableModel')
            ->get();

        $expired = ExpirationTracking::expired()
            ->with('trackableModel')
            ->get();

        return [
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
                'by_type' => $expiringSoon
                    ->concat($expired)
                    ->groupBy('expiration_type')
                    ->map->count(),
            ]
        ];
    }
    public function updateWarningSettings(array $expirationIds, int $warningDays): array
    {
        ExpirationTracking::whereIn('id', $expirationIds)
            ->update([
                'warning_days' => $warningDays
            ]);

        return [
            'success' => true,
            'message' => 'Warning settings updated for ' . count($expirationIds) . ' items'
        ];
    }
}