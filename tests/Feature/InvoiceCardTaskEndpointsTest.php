<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Clocking;
use App\Models\MaintenanceRequest;
use Modules\Invoice\Models\InvoiceCard;

class InvoiceCardTaskEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_and_remove_task_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $store = Store::factory()->create();
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);
        $card = InvoiceCard::factory()->create(['clocking_id' => $clocking->id, 'store_id' => $store->id, 'user_id' => $user->id]);
        $req = MaintenanceRequest::factory()->create(['store_id' => $store->id]);

        $addResp = $this->postJson(route('invoice.cards.add-task', $card->id), ['maintenance_request_id' => $req->id]);
        $addResp->assertStatus(200)->assertJson(['success' => true]);

        $removeResp = $this->postJson(route('invoice.cards.remove-task', $card->id), ['maintenance_request_id' => $req->id]);
        $removeResp->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_complete_task_endpoint_and_complete_all(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $store = Store::factory()->create();
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);
        $card = InvoiceCard::factory()->create(['clocking_id' => $clocking->id, 'store_id' => $store->id, 'user_id' => $user->id]);

        $req1 = MaintenanceRequest::factory()->create(['store_id' => $store->id]);
        $req2 = MaintenanceRequest::factory()->create(['store_id' => $store->id]);

        $card->maintenanceRequests()->attach([$req1->id, $req2->id]);

        $resp1 = $this->postJson(route('invoice.cards.tasks.complete', $card->id), ['maintenance_request_id' => $req1->id]);
        $resp1->assertStatus(200)->assertJson(['success' => true, 'all_tasks_complete' => false]);

        $resp2 = $this->postJson(route('invoice.cards.tasks.complete', $card->id), ['maintenance_request_id' => $req2->id]);
        $resp2->assertStatus(200)->assertJson(['success' => true, 'all_tasks_complete' => true]);

        $respAll = $this->postJson(route('invoice.cards.tasks.complete-all', $card->id));
        $respAll->assertStatus(200)->assertJson(['success' => true]);
    }
}
