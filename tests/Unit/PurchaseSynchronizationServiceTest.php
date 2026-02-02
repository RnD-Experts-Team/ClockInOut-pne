<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Clocking;
use App\Models\Payment;
use App\Services\PurchaseSynchronizationService;

class PurchaseSynchronizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_from_clocking_to_payment_creates_payment(): void
    {
        $user = \App\Models\User::factory()->create();
        $clocking = Clocking::factory()->create(['user_id' => $user->id, 'purchase_cost' => 150.00]);

        $svc = new PurchaseSynchronizationService();
        $payment = $svc->syncFromClockingToPayment($clocking);

        $this->assertNotNull($payment);
        $this->assertDatabaseHas('payments', ['clocking_id' => $clocking->id, 'cost' => 150.00, 'source_system' => 'clocking_system', 'sync_status' => 'synced']);
    }

    public function test_update_clocking_purchase_cost_sums_payments(): void
    {
        $user = \App\Models\User::factory()->create();
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);

        Payment::factory()->create(['clocking_id' => $clocking->id, 'cost' => 10.00]);
        Payment::factory()->create(['clocking_id' => $clocking->id, 'cost' => 20.00]);

        $svc = new PurchaseSynchronizationService();
        $this->assertTrue($svc->updateClockingPurchaseCost($clocking->id));

        $clocking->refresh();
        $this->assertEquals(30.00, (float) $clocking->purchase_cost);
        $this->assertTrue($clocking->bought_something);
    }
}