<?php

namespace App\Services\Api\Admin;

use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\Store;

class TaskAssignmentService
{
    public function getTaskAssignments($request)
    {
        $query = TaskAssignment::with([
            'assignedUser',
            'maintenanceRequest.store',
            'scheduleShift'
        ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->whereHas('assignedUser', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                })

                ->orWhereHas('maintenanceRequest', function ($mrQuery) use ($search) {

                    $mrQuery->where('description_of_issue', 'like', "%{$search}%")

                        ->orWhereHas('store', function ($storeQuery) use ($search) {
                            $storeQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('store_number', 'like', "%{$search}%");
                        });
                });

            });
        }

        // Filter by employee
        if ($request->filled('employee_id') && $request->employee_id !== 'all') {
            $query->where('assigned_user_id', $request->employee_id);
        }

        // Filter by store
        if ($request->filled('store_id') && $request->store_id !== 'all') {
            $query->whereHas('maintenanceRequest', function ($mrQuery) use ($request) {
                $mrQuery->where('store_id', $request->store_id);
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $taskAssignments = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        $employees = User::orderBy('name')->get();

        $stores = Store::orderBy('store_number')->get();

        return [
            'task_assignments' => $taskAssignments,
            'employees' => $employees,
            'stores' => $stores
        ];
    }
    public function getTaskAssignmentDetails($taskAssignment)
    {
        $taskAssignment->load([
            'maintenanceRequest.store',
            'assignedUser',
            'scheduleShift.schedule',
            'assignedBy'
        ]);

        return $taskAssignment;
    }
    public function updateTaskAssignment($request, $taskAssignment)
    {
        $taskAssignment->update($request->only([
            'assigned_user_id',
            'status',
            'priority',
            'due_date',
            'assignment_notes'
        ]));

        return $taskAssignment->fresh([
            'assignedUser',
            'maintenanceRequest.store',
            'scheduleShift.schedule',
            'assignedBy'
        ]);
    }
    public function updateStatus($request, TaskAssignment $taskAssignment): TaskAssignment
    {
        $taskAssignment->update([
            'status' => $request->status,
        ]);

        // Update the related maintenance request if completed
        if ($request->status === 'completed' && $taskAssignment->maintenanceRequest) {
            $taskAssignment->maintenanceRequest->update([
                'status' => 'done',
            ]);
        }

        return $taskAssignment->fresh([
            'assignedUser',
            'maintenanceRequest.store',
            'scheduleShift.schedule',
            'assignedBy',
        ]);
    }
}