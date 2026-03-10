<?php

namespace App\Services\Api;

use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;

class UserService
{

    public function index()
    {
        return User::all();
    }


    public function store($validated)
    {
        $validated['password'] = Hash::make($validated['password']);

        return User::create($validated);
    }



    public function update($validated, $request, User $user)
    {

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        // Sync managed stores if provided

        if ($request->has('managed_stores')) {
            // Prepare pivot data with assigned_by and assigned_at

            $pivotData = [];

            foreach ($request->managed_stores as $storeId) {

                $pivotData[$storeId] = [
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ];
            }

            $user->managedStores()->sync($pivotData);

        } else {
            // If no stores selected, detach all
            $user->managedStores()->detach();
        }

        return $user;
    }


    public function destroy(User $user)
    {
        // Delete all related records first
        $user->statusHistories()->delete();
        $user->taskAssignments()->delete();
        $user->shifts()->delete();
        // Now delete the user

        $user->delete();

        return true;
    }

}