<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Clocking;
use App\Models\Payment;
use App\Models\User;

class PaymentSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_with_clocking_links_and_updates_clocking_cost(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $clocking = Clocking::factory()->create(['user_id' => $user->id]);

        $response = $this->post(route('payments.store'), [
            'company_id' => \App\Models\Company::factory()->create()->id,
            'date' => now()->toDateString(),
            'cost' => 42.50,
            'clocking_id' => $clocking->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('payments', ['clocking_id' => $clocking->id, 'cost' => 42.50, 'source_system' => 'clocking_system']);

        $clocking->refresh();
        $this->assertEquals(42.50, (float) $clocking->purchase_cost);
        $this->assertTrue($clocking->bought_something);
    }
}