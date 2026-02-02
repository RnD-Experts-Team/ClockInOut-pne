<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Clocking;
use App\Models\MaintenanceRequest;
use Modules\Invoice\Models\InvoiceCard;
use Modules\Invoice\Models\InvoiceCardTask;

class MultiTaskCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_attach_multiple_tasks_and_mark_one_complete(): void
    {
        $technician = User::factory()->create(['role' => 'user', 'hourly_pay' => 20]);
        $this->actingAs($technician);

        $store = Store::factory()->create();

        $clocking = Clocking::factory()->create([
            'user_id' => $technician->id,
            'using_car' => false,
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $store->id,
            'status' => 'in_progress'
        ]);

        $req1 = MaintenanceRequest::factory()->create(['store_id' => $store->id, 'status' => 'in_progress', 'assigned_to' => $technician->id]);
        $req2 = MaintenanceRequest::factory()->create(['store_id' => $store->id, 'status' => 'in_progress', 'assigned_to' => $technician->id]);

        // Attach both maintenance requests to single card
        $card->maintenanceRequests()->attach([$req1->id, $req2->id]);

        $this->assertCount(2, $card->maintenanceRequests);
        $this->assertCount(2, $card->tasks);

        // Mark first attached task complete using InvoiceCardTask model
        $task = InvoiceCardTask::where('invoice_card_id', $card->id)->where('maintenance_request_id', $req1->id)->first();
        $this->assertNotNull($task);
        $this->assertEquals('pending', $task->task_status);

        $task->markComplete();

        $task->refresh();
        $this->assertEquals('completed', $task->task_status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_ticket_completion_requires_all_task_rows_completed(): void
    {
        $technician = User::factory()->create(['role' => 'user', 'hourly_pay' => 20]);
        $this->actingAs($technician);

        $store = Store::factory()->create();

        $clocking = Clocking::factory()->create(['user_id' => $technician->id]);

        $card1 = InvoiceCard::factory()->create(['clocking_id' => $clocking->id, 'store_id' => $store->id, 'status' => 'in_progress']);
        $card2 = InvoiceCard::factory()->create(['clocking_id' => $clocking->id, 'store_id' => $store->id, 'status' => 'in_progress']);

        $req = MaintenanceRequest::factory()->create(['store_id' => $store->id, 'status' => 'in_progress', 'assigned_to' => $technician->id]);

        $card1->maintenanceRequests()->attach($req->id);
        $card2->maintenanceRequests()->attach($req->id);

        $service = app(\App\Services\TicketCompletionService::class);

        // Complete first task row only
        $task1 = InvoiceCardTask::where('invoice_card_id', $card1->id)->where('maintenance_request_id', $req->id)->first();
        $task1->markComplete();

        $this->assertFalse($service->checkTicketCompletion($req->id));

        // Complete second task row
        $task2 = InvoiceCardTask::where('invoice_card_id', $card2->id)->where('maintenance_request_id', $req->id)->first();
        $task2->markComplete();

        $this->assertTrue($service->checkTicketCompletion($req->id));

        // Update ticket status via service
        $this->assertTrue($service->updateTicketStatus($req->id));

        $req->refresh();
        $this->assertEquals('done', $req->status);

        // Both pivot rows should have task_status = 'completed'
        $rows = \DB::table('invoice_card_maintenance_requests')->where('maintenance_request_id', $req->id)->get();
        foreach ($rows as $row) {
            $this->assertEquals('completed', $row->task_status);
            $this->assertNotNull($row->completed_at);
        }
    }
}
