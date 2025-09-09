<?php
// app/Http/Controllers/TaskAssignmentController.php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Models\TaskAssignment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = TaskAssignment::with([
            'assignedUser',
            'maintenanceRequest.store',
            'scheduleShift'
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('assignedUser', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                })
                    ->orWhereHas('maintenanceRequest', function($mrQuery) use ($search) {
                        $mrQuery->where('description_of_issue', 'like', "%{$search}%")
                            ->orWhereHas('store', function($storeQuery) use ($search) {
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
            $query->whereHas('maintenanceRequest', function($mrQuery) use ($request) {
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

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $taskAssignments = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $employees = User::orderBy('name')->get();
        $stores = Store::orderBy('store_number')->get();

        return view('admin.task-assignments.index', compact('taskAssignments', 'employees', 'stores'));
    }

    // Other methods (show, updateStatus, edit, update) remain unchanged




    public function show(TaskAssignment $taskAssignment)
    {
        $taskAssignment->load([
            'maintenanceRequest.store',
            'assignedUser',
            'scheduleShift.schedule',
            'assignedBy'
        ]);

        return view('admin.task-assignments.show', compact('taskAssignment'));
    }

    public function updateStatus(Request $request, TaskAssignment $taskAssignment)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $taskAssignment->update([
            'status' => $request->status
        ]);

        // Update the related maintenance request if completed
        if ($request->status === 'completed') {
            $taskAssignment->maintenanceRequest->update([
                'status' => 'done'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Task status updated successfully!');
    }
    public function edit(TaskAssignment $taskAssignment)
    {
        $taskAssignment->load([
            'assignedUser',
            'maintenanceRequest.store',
            'scheduleShift'
        ]);

        $employees = User::all();

        return view('admin.task-assignments.edit', compact('taskAssignment', 'employees'));
    }

    public function update(Request $request, TaskAssignment $taskAssignment)
    {
        $request->validate([
            'assigned_user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:normal,high,urgent',
            'due_date' => 'nullable|date|after:now',
            'assignment_notes' => 'nullable|string|max:1000'
        ]);

        $taskAssignment->update($request->only([
            'assigned_user_id',
            'status',
            'priority',
            'due_date',
            'assignment_notes'
        ]));

        return redirect()
            ->route('admin.task-assignments.show', $taskAssignment)
            ->with('success', 'Task assignment updated successfully.');
    }
}
