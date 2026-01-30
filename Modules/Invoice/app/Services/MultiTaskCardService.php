<?php

namespace Modules\Invoice\Services;

use Modules\Invoice\Models\InvoiceCardTask;
use Modules\Invoice\Models\InvoiceCard;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\Log;

class MultiTaskCardService
{
    public function addTaskToCard(int $cardId, int $taskId)
    {
        $card = InvoiceCard::find($cardId);
        if (!$card) {
            return ['success' => false, 'message' => 'Card not found'];
        }

        $existing = InvoiceCardTask::where('invoice_card_id', $cardId)
            ->where('maintenance_request_id', $taskId)
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Task already attached'];
        }

        $task = InvoiceCardTask::create([
            'invoice_card_id' => $cardId,
            'maintenance_request_id' => $taskId,
            'task_status' => 'pending'
        ]);

        return ['success' => true, 'task' => $task];
    }

    public function removeTaskFromCard(int $cardId, int $taskId)
    {
        $task = InvoiceCardTask::where('invoice_card_id', $cardId)
            ->where('maintenance_request_id', $taskId)
            ->first();

        if (!$task) {
            return ['success' => false, 'message' => 'Task not found on card'];
        }

        try {
            $task->delete();
            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to remove task from card', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to remove task'];
        }
    }

    public function getCardTasks(int $cardId)
    {
        return InvoiceCardTask::with('maintenanceRequest')
            ->where('invoice_card_id', $cardId)
            ->get();
    }

    public function markTaskComplete(int $cardId, int $taskId)
    {
        $task = InvoiceCardTask::where('invoice_card_id', $cardId)
            ->where('maintenance_request_id', $taskId)
            ->first();

        if (!$task) {
            return ['success' => false, 'message' => 'Task not found'];
        }

        $task->markComplete();

        $allComplete = $this->areAllTasksComplete($cardId);

        return ['success' => true, 'all_tasks_complete' => $allComplete, 'task' => $task];
    }

    public function markAllTasksComplete(int $cardId)
    {
        $tasks = InvoiceCardTask::where('invoice_card_id', $cardId)->get();

        foreach ($tasks as $task) {
            $task->markComplete();
        }

        return ['success' => true];
    }

    public function areAllTasksComplete(int $cardId): bool
    {
        $incomplete = InvoiceCardTask::where('invoice_card_id', $cardId)
            ->where('task_status', '!=', 'completed')
            ->count();

        return $incomplete === 0;
    }
}
