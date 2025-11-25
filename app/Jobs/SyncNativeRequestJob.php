<?php
namespace App\Jobs;

use App\Models\MaintenanceRequest;
use App\Models\Native\NativeRequest;
use App\Models\Native\NativeRequestAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncNativeRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $maintenanceRequestId;

    public function __construct(int $maintenanceRequestId)
    {
        $this->maintenanceRequestId = $maintenanceRequestId;
    }

    public function handle()
    {
        $mr = MaintenanceRequest::with(['attachments', 'urgencyLevel', 'requester', 'store'])->find($this->maintenanceRequestId);
        if (! $mr) {
            Log::warning('SyncNativeRequestJob: MaintenanceRequest not found', ['id' => $this->maintenanceRequestId]);
            return;
        }

        // Validate that the requester_id exists in users table to avoid foreign key constraint violation
        if ($mr->requester_id && !\App\Models\User::where('id', $mr->requester_id)->exists()) {
            Log::warning('SyncNativeRequestJob: Requester user not found, skipping sync', [
                'maintenance_request_id' => $mr->id,
                'requester_id' => $mr->requester_id,
            ]);
            return;
        }

        // Check for existing native request by maintenance_request_id
        $native = NativeRequest::where('maintenance_request_id', $mr->id)->first();

        // Build external requester name from CognitoForms requester
        $externalRequester = null;
        if ($mr->requester) {
            $externalRequester = trim($mr->requester->first_name . ' ' . $mr->requester->last_name);
        }

        $data = [
            'store_id' => $mr->store_id,
            'requester_id' => $mr->requester_id,
            'external_requester' => $externalRequester,
            'is_from_cognito' => true,
            'equipment_with_issue' => $mr->equipment_with_issue,
            'description_of_issue' => $mr->description_of_issue,
            'urgency_level_id' => $this->mapUrgency($mr->urgencyLevel),
            'basic_troubleshoot_done' => (bool) $mr->basic_troubleshoot_done,
            'request_date' => $mr->request_date ?? $mr->date_submitted,
            'status' => $mr->status,
            'assigned_to' => $mr->assigned_to,
            'costs' => $mr->costs,
            'how_we_fixed_it' => $mr->how_we_fixed_it,
            'maintenance_request_id' => $mr->id,
        ];

        if ($native) {
            $native->update($data);
        } else {
            $native = NativeRequest::create($data);
        }

        // Sync attachments: create pointer records if not exist
        foreach ($mr->attachments as $att) {
            $exists = NativeRequestAttachment::where('native_request_id', $native->id)->where('file_name', $att->file_name)->first();
            if ($exists) continue;

            // Prefer storing the download_url as file_path pointer. If you want to copy files into storage,
            // implement download logic here (use Storage::put and update file_path accordingly).
            NativeRequestAttachment::create([
                'native_request_id' => $native->id,
                'file_name' => $att->file_name,
                'file_path' => $att->download_url ?? $att->file_name,
                'file_size' => $att->file_size ?? 0,
                'mime_type' => $att->content_type ?? null,
            ]);
        }

        Log::info('SyncNativeRequestJob completed', ['maintenance_request_id' => $mr->id, 'native_request_id' => $native->id]);
    }

    protected function mapUrgency($urgencyLevel)
    {
        if (! $urgencyLevel) return null;

        // Try to map by name first
        $nativeModel = \App\Models\Native\NativeUrgencyLevel::where('name', $urgencyLevel->name)->first();
        if ($nativeModel) return $nativeModel->id;

        // Try by level numeric
        if (isset($urgencyLevel->level)) {
            $nativeModel = \App\Models\Native\NativeUrgencyLevel::where('level', $urgencyLevel->level)->first();
            if ($nativeModel) return $nativeModel->id;
        }

        // Fallback to first native urgency
        return \App\Models\Native\NativeUrgencyLevel::first()?->id ?? null;
    }
}
