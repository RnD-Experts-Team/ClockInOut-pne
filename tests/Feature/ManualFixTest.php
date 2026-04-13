<?php

namespace Tests\Feature;

use App\Models\Clocking;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invoice\Models\InvoiceCard;
use Tests\TestCase;

class ManualFixTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function tech(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    private function makeCard(User $user, Store $store): InvoiceCard
    {
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);

        return InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id'    => $store->id,
            'user_id'     => $user->id,
            'status'      => 'in_progress',
        ]);
    }

    // ── storeManualFix ────────────────────────────────────────────────────────

    public function test_technician_can_create_manual_fix_record(): void
    {
        $user  = $this->tech();
        $store = Store::factory()->create();
        $eq    = Equipment::create(['name' => 'Ice Machine', 'store_id' => $store->id, 'is_active' => true]);
        $card  = $this->makeCard($user, $store);

        $this->actingAs($user);

        $this->post(route('invoice.cards.manual-fix.store', $card->id), [
            'equipment_id'         => $eq->id,
            'description_of_issue' => 'Machine stopped working.',
            'how_we_fixed_it'      => 'Replaced compressor.',
        ])->assertRedirect();

        $this->assertDatabaseHas('maintenance_requests', [
            'store_id'             => $store->id,
            'equipment_id'         => $eq->id,
            'description_of_issue' => 'Machine stopped working.',
            'source'               => 'manual',
            'status'               => 'in_progress',
            'created_by_user_id'   => $user->id,
            'not_in_cognito'       => 1,
        ]);
    }

    public function test_manual_mr_attached_to_card_via_pivot(): void
    {
        $user  = $this->tech();
        $store = Store::factory()->create();
        $eq    = Equipment::create(['name' => 'Fryer', 'store_id' => $store->id, 'is_active' => true]);
        $card  = $this->makeCard($user, $store);

        $this->actingAs($user);

        $this->post(route('invoice.cards.manual-fix.store', $card->id), [
            'equipment_id'         => $eq->id,
            'description_of_issue' => 'Oil leak.',
        ]);

        $mr = MaintenanceRequest::where('description_of_issue', 'Oil leak.')->first();
        $this->assertNotNull($mr);

        $this->assertDatabaseHas('invoice_card_maintenance_requests', [
            'invoice_card_id'        => $card->id,
            'maintenance_request_id' => $mr->id,
            'task_status'            => 'pending',
            'status'                 => 'not_done',
        ]);
    }

    public function test_how_we_fixed_it_is_optional(): void
    {
        $user  = $this->tech();
        $store = Store::factory()->create();
        $eq    = Equipment::create(['name' => 'HVAC', 'store_id' => $store->id, 'is_active' => true]);
        $card  = $this->makeCard($user, $store);

        $this->actingAs($user);

        $this->post(route('invoice.cards.manual-fix.store', $card->id), [
            'equipment_id'         => $eq->id,
            'description_of_issue' => 'Filter clogged.',
            // 'how_we_fixed_it' omitted intentionally
        ])->assertRedirect();

        $this->assertDatabaseHas('maintenance_requests', [
            'description_of_issue' => 'Filter clogged.',
            'source'               => 'manual',
        ]);
    }

    public function test_validation_rejects_missing_equipment_id(): void
    {
        $user  = $this->tech();
        $store = Store::factory()->create();
        $card  = $this->makeCard($user, $store);

        $this->actingAs($user);

        $this->post(route('invoice.cards.manual-fix.store', $card->id), [
            'description_of_issue' => 'Broken.',
        ])->assertSessionHasErrors('equipment_id');
    }

    public function test_validation_rejects_missing_description(): void
    {
        $user  = $this->tech();
        $store = Store::factory()->create();
        $eq    = Equipment::create(['name' => 'Dryer', 'store_id' => $store->id, 'is_active' => true]);
        $card  = $this->makeCard($user, $store);

        $this->actingAs($user);

        $this->post(route('invoice.cards.manual-fix.store', $card->id), [
            'equipment_id' => $eq->id,
        ])->assertSessionHasErrors('description_of_issue');
    }

    public function test_other_technician_cannot_add_fix_to_anothers_card(): void
    {
        $owner = $this->tech();
        $other = $this->tech();
        $store = Store::factory()->create();
        $eq    = Equipment::create(['name' => 'Fan', 'store_id' => $store->id, 'is_active' => true]);
        $card  = $this->makeCard($owner, $store);

        $this->actingAs($other);

        $this->post(route('invoice.cards.manual-fix.store', $card->id), [
            'equipment_id'         => $eq->id,
            'description_of_issue' => 'Fan not spinning.',
        ])->assertForbidden();
    }

    // ── Manual MR appears in Equipment Tracker counts ─────────────────────────

    public function test_manual_mr_counted_under_equipment(): void
    {
        $user  = $this->tech();
        $store = Store::factory()->create();
        $eq    = Equipment::create(['name' => 'Oven', 'store_id' => $store->id, 'is_active' => true]);

        // Direct DB creation with source=manual to bypass route auth
        MaintenanceRequest::create([
            'store_id'             => $store->id,
            'equipment_id'         => $eq->id,
            'equipment_with_issue' => 'Oven',
            'description_of_issue' => 'Not heating.',
            'source'               => 'manual',
            'status'               => 'in_progress',
            'created_by_user_id'   => $user->id,
            'request_date'         => now(),
            'date_submitted'       => now(),
            'not_in_cognito'       => true,
        ]);

        $count = MaintenanceRequest::where('equipment_id', $eq->id)->count();
        $this->assertEquals(1, $count);
    }

    // ── Manual badge / Show page content ──────────────────────────────────────

    public function test_manual_mr_has_source_manual_in_db(): void
    {
        $user  = $this->tech();
        $store = Store::factory()->create();
        $eq    = Equipment::create(['name' => 'Steamer', 'store_id' => $store->id, 'is_active' => true]);
        $card  = $this->makeCard($user, $store);

        $this->actingAs($user);

        $this->post(route('invoice.cards.manual-fix.store', $card->id), [
            'equipment_id'         => $eq->id,
            'description_of_issue' => 'Steam leak.',
        ]);

        $mr = MaintenanceRequest::where('description_of_issue', 'Steam leak.')->first();
        $this->assertEquals('manual', $mr->source);
        $this->assertEquals($user->id, $mr->created_by_user_id);
    }
}
