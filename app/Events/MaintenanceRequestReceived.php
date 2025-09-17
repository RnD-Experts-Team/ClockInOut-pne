<?php
// app/Events/MaintenanceRequestReceived.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Important: ShouldBroadcastNow
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceRequestReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $store_name;
    public $equipment;
    public $id;

    public function __construct($maintenanceRequest, string $notificationType = 'new_request')
    {
        // Simplified - avoid relationship issues
        $this->id = $maintenanceRequest->id;
        $this->message = "🔧 New maintenance request received";
        $this->store_name = $maintenanceRequest->store->name ?? 'Unknown Store';
        $this->equipment = $maintenanceRequest->equipment_with_issue ?? 'Unknown Equipment';
    }

    public function broadcastOn()
    {
        return new Channel('maintenance-notifications'); // Single channel for now
    }

    public function broadcastAs()
    {
        return 'maintenance.request.received';
    }
}
