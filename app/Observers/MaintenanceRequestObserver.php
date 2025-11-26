<?php
namespace App\Observers;

use App\Models\MaintenanceRequest;
use App\Services\RequestSyncService;

class MaintenanceRequestObserver
{
    protected RequestSyncService $service;

    public function __construct()
    {
        $this->service = new RequestSyncService();
    }

    public function created(MaintenanceRequest $maintenanceRequest)
    {
        $this->service->syncMaintenanceRequest($maintenanceRequest);
    }

    public function updated(MaintenanceRequest $maintenanceRequest)
    {
        $this->service->syncMaintenanceRequest($maintenanceRequest);
    }

    public function deleted(MaintenanceRequest $maintenanceRequest)
    {
        // Optionally handle deletion: you may want to soft-delete or mark native as canceled
    }
}
