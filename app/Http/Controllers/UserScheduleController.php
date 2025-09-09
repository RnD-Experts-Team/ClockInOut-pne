<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleShift;
use App\Models\TaskAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get current week or requested week
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfWeek()
            : Carbon::now()->startOfWeek();

        $endDate = $startDate->copy()->endOfWeek();

        // Get user's shifts for the week with relationships
        $scheduleShifts = ScheduleShift::with([
            'schedule',
            'user',
            'taskAssignments.maintenanceRequest.store',
            'taskAssignments.assignedUser'
        ])
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Get user's active task assignments
        $taskAssignments = TaskAssignment::with([
            'maintenanceRequest.store',
            'maintenanceRequest.urgencyLevel',
            'assignedUser'
        ])
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // Create week calendar with proper date filtering
        $weekDays = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Filter shifts for this specific day
            $dayShifts = $scheduleShifts->filter(function ($shift) use ($date) {
                return $shift->date && $shift->date->isSameDay($date);
            });

            $weekDays->push([
                'date' => $date->copy(),
                'shifts' => $dayShifts
            ]);
        }

        // Calculate weekly stats
        $weeklyHours = $scheduleShifts->sum('duration_hours');
        $tasksThisWeek = $taskAssignments->count();

        return view('user.schedule.index', compact(
            'scheduleShifts',
            'taskAssignments',
            'weekDays',
            'weeklyHours',
            'tasksThisWeek',
            'startDate',
            'endDate'
        ));
    }
}
