<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\Invoice\Services\MultiTaskCardService;
use Modules\Invoice\Models\InvoiceCard;
use Modules\Invoice\Models\InvoiceCardTask;
use App\Models\Clocking;
use App\Models\Store;
use App\Models\MaintenanceRequest;
use App\Models\User;

class MultiTaskCardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_and_remove_task(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $store = Store::factory()->create();
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);

        $card = InvoiceCard::factory()->create(['clocking_id' => $clocking->id, 'store_id' => $store->id]);
        $req = MaintenanceRequest::factory()->create(['store_id' => $store->id]);

        $service = new MultiTaskCardService();

        $add = $service->addTaskToCard($card->id, $req->id);
        $this->assertTrue($add['success']);
        $this->assertDatabaseHas('invoice_card_maintenance_requests', ['invoice_card_id' => $card->id, 'maintenance_request_id' => $req->id]);

        $remove = $service->removeTaskFromCard($card->id, $req->id);
        $this->assertTrue($remove['success']);
        $this->assertDatabaseMissing('invoice_card_maintenance_requests', ['invoice_card_id' => $card->id, 'maintenance_request_id' => $req->id]);
    }

    public function test_mark_task_complete_and_check_all_complete(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $store = Store::factory()->create();
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);

        $card = InvoiceCard::factory()->create(['clocking_id' => $clocking->id, 'store_id' => $store->id]);
        $req1 = MaintenanceRequest::factory()->create(['store_id' => $store->id]);
        $req2 = MaintenanceRequest::factory()->create(['store_id' => $store->id]);

        $card->maintenanceRequests()->attach([$req1->id, $req2->id]);

        $service = new MultiTaskCardService();

        $res1 = $service->markTaskComplete($card->id, $req1->id);
        $this->assertTrue($res1['success']);
        $this->assertFalse($service->areAllTasksComplete($card->id));

        $res2 = $service->markTaskComplete($card->id, $req2->id);
        $this->assertTrue($res2['success']);
        $this->assertTrue($service->areAllTasksComplete($card->id));
    }
}
