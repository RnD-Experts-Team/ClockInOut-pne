<?php
// app/Notifications/WebhookNotification.php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WebhookNotification extends Notification
{
    use Queueable;

    protected $maintenanceRequest;
    protected $notificationType;

    public function __construct(MaintenanceRequest $maintenanceRequest, string $notificationType = 'new_request')
    {
        $this->maintenanceRequest = $maintenanceRequest;
        $this->notificationType = $notificationType;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'type' => $this->notificationType,
            'message' => $this->generateMessage(),
            'store_name' => $this->maintenanceRequest->store->name ?? 'Unknown Store',
            'urgency_level' => $this->maintenanceRequest->urgencyLevel->name,
            'equipment' => $this->maintenanceRequest->equipment_with_issue,
            'requester_name' => $this->maintenanceRequest->requester->first_name . ' ' . $this->maintenanceRequest->requester->last_name,
            'is_urgent' => $this->maintenanceRequest->urgencyLevel->name === 'Urgent',
            'created_at' => $this->maintenanceRequest->created_at->toDateTimeString()
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->maintenanceRequest->id, // This should be the notification ID
            'maintenance_request_id' => $this->maintenanceRequest->id, // Explicitly include maintenance_request_id
            'message' => $this->generateMessage(),
            'type' => $this->notificationType,
            'store_name' => $this->maintenanceRequest->store->name ?? 'Unknown Store',
            'equipment' => $this->maintenanceRequest->equipment_with_issue,
            'urgency_level' => $this->maintenanceRequest->urgencyLevel->name,
            'is_urgent' => $this->maintenanceRequest->urgencyLevel->name === 'Urgent',
            'created_at' => $this->maintenanceRequest->created_at->toDateTimeString(),
            'read_at' => null
        ]);
    }

    private function generateMessage(): string
    {
        $storeName = $this->maintenanceRequest->store->name ?? 'Store';
        $urgency = $this->maintenanceRequest->urgencyLevel->name;

        if ($urgency === 'Urgent') {
            return "🚨 URGENT: New maintenance request from {$storeName}";
        }

        return "🔧 New maintenance request received from {$storeName}";
    }
}
