<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\BulkReminderActionRequest;
use App\Http\Requests\Api\Admin\SnoozeCalendarReminderRequest;
use App\Http\Requests\Api\Admin\StoreCalendarReminderRequest;
use App\Http\Requests\Api\Admin\UpdateCalendarReminderRequest;
use App\Models\CalendarReminder;
use App\Services\Api\Admin\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    protected ReminderService $service;

    public function __construct(ReminderService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {

            $data = $this->service->getReminders();
            $reminders = $data['reminders'];

            return response()->json([
                'success' => true,
                'data' => $reminders->items(),
                'pagination' => [
                    'current_page' => $reminders->currentPage(),
                    'last_page' => $reminders->lastPage(),
                    'per_page' => $reminders->perPage(),
                    'total' => $reminders->total(),
                ],
                'statistics' => $data['statistics']
            ]);

        } catch (\Exception $e) {

            Log::error('Calendar Reminder Index Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reminders'
            ], 500);
        }
    }
  
    public function store(StoreCalendarReminderRequest $request): JsonResponse
    {
        try {

            $result = $this->service->store(
                $request->validated(),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['reminder']
            ], 201);

        } catch (\Exception $e) {

            Log::error('Reminder Create Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create reminder'
            ], 500);
        }
    }
    public function show(CalendarReminder $reminder): JsonResponse
    {
        try {

            $reminder = $this->service->getReminder($reminder);

            return response()->json([
                'success' => true,
                'data' => $reminder
            ]);

        } catch (\Exception $e) {

            Log::error('Reminder Show Error', [
                'message' => $e->getMessage(),
                'reminder_id' => $reminder->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reminder'
            ], 500);
        }
    }
    public function update(UpdateCalendarReminderRequest $request,CalendarReminder $reminder): JsonResponse
    {
        try {

            $result = $this->service->updateReminder(
                $request->validated(),
                $reminder,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['reminder']
            ]);

        } catch (\Exception $e) {

            Log::error('Reminder Update Error', [
                'message' => $e->getMessage(),
                'reminder_id' => $reminder->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update reminder'
            ], 500);
        }
    }
    public function dismiss(CalendarReminder $reminder): \Illuminate\Http\JsonResponse
    {
        try {

            $result = $this->service->dismissReminder($reminder);

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {

            Log::error('Reminder Dismiss Error', [
                'message' => $e->getMessage(),
                'reminder_id' => $reminder->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss reminder'
            ], 500);
        }
    }


    public function snooze(SnoozeCalendarReminderRequest $request,CalendarReminder $reminder):JsonResponse
    {
        try {

            $minutes = $request->validated()['minutes'];

            $result = $this->service->snoozeReminder($reminder, $minutes);

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {

            \Log::error('Reminder Snooze Error', [
                'message' => $e->getMessage(),
                'reminder_id' => $reminder->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to snooze reminder'
            ], 500);
        }
    }
    public function markAsRead(CalendarReminder $reminder): JsonResponse
    {
        try {

            $result = $this->service->markAsRead($reminder);

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {

            Log::error('Reminder Mark As Read Error', [
                'message' => $e->getMessage(),
                'reminder_id' => $reminder->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark reminder as read'
            ], 500);
        }
    }
    public function getDueReminders(): \Illuminate\Http\JsonResponse
    {
        try {

            $data = $this->service->getDueReminders(auth()->id());

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {

            \Log::error('Get Due Reminders Error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch due reminders'
            ], 500);
        }
    }
    public function bulkAction(BulkReminderActionRequest $request): JsonResponse
    {
        try {

            $result = $this->service->bulkAction(
                $request->validated(),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'affected' => $result['count']
            ]);

        } catch (\Exception $e) {

            Log::error('Reminder Bulk Action Error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action'
            ], 500);
        }
    }
}