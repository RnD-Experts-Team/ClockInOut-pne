<?php

namespace Tests\Unit;

use App\Models\Clocking;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\Store;
use App\Models\User;
use App\Services\EquipmentLaborService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Invoice\Models\InvoiceCard;
use Tests\TestCase;

class EquipmentLaborServiceTest extends TestCase
{
    use RefreshDatabase;

    private EquipmentLaborService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EquipmentLaborService();
    }

    // ── Helper: create a bare equipment record ────────────────────────────────

    private function makeEquipment(array $attrs = []): Equipment
    {
        return Equipment::create(array_merge([
            'name'     => 'Test Equipment',
            'is_active' => true,
        ], $attrs));
    }

    // ── Helper: create a card with one or more MRs attached ──────────────────

    private function makeCard(User $user, Store $store, array $cardAttrs = []): InvoiceCard
    {
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);

        return InvoiceCard::create(array_merge([
            'clocking_id'              => $clocking->id,
            'store_id'                 => $store->id,
            'user_id'                  => $user->id,
            'start_time'               => now(),
            'end_time'                 => now()->addHours(2),
            'labor_hours'              => 2.00,
            'accumulated_labor_hours'  => 0.00,
            'labor_cost'               => 40.00,
            'driving_time_payment'     => 10.00,
            'mileage_payment'          => 5.00,
            'materials_cost'           => 0.00,
            'total_cost'               => 55.00,
            'status'                   => 'completed',
        ], $cardAttrs));
    }

    private function attachMrToCard(InvoiceCard $card, MaintenanceRequest $mr, string $taskStatus = 'completed'): void
    {
        DB::table('invoice_card_maintenance_requests')->insert([
            'invoice_card_id'        => $card->id,
            'maintenance_request_id' => $mr->id,
            'task_status'            => $taskStatus,
            'status'                 => 'not_done',
            'completed_at'           => $taskStatus === 'completed' ? now() : null,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);
    }

    // ── Test 7.1: Single-session, single MR on card ───────────────────────────

    public function test_single_session_single_mr_repair_and_labor(): void
    {
        $user  = User::factory()->create();
        $store = Store::factory()->create();
        $mr    = MaintenanceRequest::factory()->create(['store_id' => $store->id]);
        $card  = $this->makeCard($user, $store, [
            'labor_hours'         => 3.00,
            'labor_cost'          => 60.00,
            'driving_time_payment'=> 10.00,
            'mileage_payment'     => 5.00,
        ]);
        $this->attachMrToCard($card, $mr, 'completed');

        $result = $this->service->calculateLabor($mr->id);

        // N=1, so all goes to this MR
        $this->assertEquals(3.00, $result['repair_hours']);
        $this->assertEquals(75.00, $result['labor_cost']); // 60 + 10 + 5
    }

    // ── Test 7.2: Single-session, multiple MRs on card (N > 1) ───────────────

    public function test_single_session_multiple_mrs_splits_equally(): void
    {
        $user  = User::factory()->create();
        $store = Store::factory()->create();
        $mr1   = MaintenanceRequest::factory()->create(['store_id' => $store->id]);
        $mr2   = MaintenanceRequest::factory()->create(['store_id' => $store->id]);
        $card  = $this->makeCard($user, $store, [
            'labor_hours'         => 4.00,
            'labor_cost'          => 80.00,
            'driving_time_payment'=> 20.00,
            'mileage_payment'     => 10.00,
        ]);
        $this->attachMrToCard($card, $mr1, 'completed');
        $this->attachMrToCard($card, $mr2, 'completed');

        $result1 = $this->service->calculateLabor($mr1->id);
        $result2 = $this->service->calculateLabor($mr2->id);

        // N=2: each gets half
        $this->assertEquals(2.00, $result1['repair_hours']);
        $this->assertEquals(55.00, $result1['labor_cost']); // (80+20+10)/2 = 55

        $this->assertEquals(2.00, $result2['repair_hours']);
        $this->assertEquals(55.00, $result2['labor_cost']);
    }

    // ── Test 7.3: Multi-session, final card completed ─────────────────────────

    public function test_multi_session_only_final_card_contributes_labor_hours(): void
    {
        $user  = User::factory()->create();
        $store = Store::factory()->create();
        $mr    = MaintenanceRequest::factory()->create(['store_id' => $store->id]);

        // Intermediate card (task_status = 'in_progress')
        $card1 = $this->makeCard($user, $store, [
            'start_time'           => now()->subDay(),
            'end_time'             => now()->subDay()->addHours(2),
            'labor_hours'          => 2.00,
            'accumulated_labor_hours' => 0.00,
            'labor_cost'           => 40.00,
            'driving_time_payment' => 10.00,
            'mileage_payment'      => 5.00,
            'status'               => 'in_progress',
        ]);
        $this->attachMrToCard($card1, $mr, 'in_progress');

        // Final card (task_status = 'completed', accumulated = intermediate hours)
        $card2 = $this->makeCard($user, $store, [
            'start_time'              => now(),
            'end_time'                => now()->addHours(1),
            'labor_hours'             => 1.00,
            'accumulated_labor_hours' => 2.00,   // carries the prior 2 hours
            'labor_cost'              => 20.00,
            'driving_time_payment'    => 8.00,
            'mileage_payment'         => 4.00,
            'status'                  => 'completed',
        ]);
        $this->attachMrToCard($card2, $mr, 'completed');

        $result = $this->service->calculateLabor($mr->id);

        // Repair hours = (1 + 2) / N = 3 hours (N=1 for final card)
        $this->assertEquals(3.00, $result['repair_hours']);

        // Labor cost from final: (20 + 8 + 4) / 1 = 32
        // Driving+mileage from intermediate: (10 + 5) / 1 = 15
        // Total labor cost = 32 + 15 = 47
        $this->assertEquals(47.00, $result['labor_cost']);
    }

    // ── Test 7.4: Multi-session, MR never completed — fallback to latest card ─

    public function test_multi_session_never_completed_falls_back_to_latest_card(): void
    {
        $user  = User::factory()->create();
        $store = Store::factory()->create();
        $mr    = MaintenanceRequest::factory()->create(['store_id' => $store->id]);

        $card = $this->makeCard($user, $store, [
            'labor_hours'         => 2.00,
            'accumulated_labor_hours' => 0.00,
            'labor_cost'          => 40.00,
            'driving_time_payment'=> 10.00,
            'mileage_payment'     => 5.00,
            'status'              => 'in_progress',
        ]);
        // task_status is never set to 'completed'
        $this->attachMrToCard($card, $mr, 'in_progress');

        // Should not throw — falls back gracefully
        $result = $this->service->calculateLabor($mr->id);

        $this->assertEquals(2.00,  $result['repair_hours']);
        $this->assertEquals(55.00, $result['labor_cost']); // 40+10+5
    }

    // ── Test 7.5: Purchase cost — admin payments only ─────────────────────────

    public function test_purchase_cost_admin_payments_only(): void
    {
        $store = Store::factory()->create();
        $mr    = MaintenanceRequest::factory()->create(['store_id' => $store->id]);

        $company = \App\Models\Company::create(['name' => 'Test Co', 'is_active' => true]);

        DB::table('payments')->insert([
            'maintenance_request_id' => $mr->id,
            'cost'                   => 150.00,
            'store_id'               => $store->id,
            'company_id'             => $company->id,
            'date'                   => now()->toDateString(),
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $cost = $this->service->calculatePurchaseCost($mr->id);

        $this->assertEquals(150.00, $cost);
    }

    // ── Test 7.6: Purchase cost — technician materials only ───────────────────

    public function test_purchase_cost_technician_materials_only(): void
    {
        $user  = User::factory()->create();
        $store = Store::factory()->create();
        $mr    = MaintenanceRequest::factory()->create(['store_id' => $store->id]);
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);
        $card  = $this->makeCard($user, $store);

        DB::table('invoice_card_materials')->insert([
            'invoice_card_id'        => $card->id,
            'maintenance_request_id' => $mr->id,
            'item_name'              => 'Test Part',
            'cost'                   => 75.00,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $cost = $this->service->calculatePurchaseCost($mr->id);

        $this->assertEquals(75.00, $cost);
    }

    // ── Test 7.7: Purchase cost — both sources combined ───────────────────────

    public function test_purchase_cost_combines_both_sources(): void
    {
        $user    = User::factory()->create();
        $store   = Store::factory()->create();
        $mr      = MaintenanceRequest::factory()->create(['store_id' => $store->id]);
        $card    = $this->makeCard($user, $store);
        $company = \App\Models\Company::create(['name' => 'Test Co Combined', 'is_active' => true]);

        DB::table('payments')->insert([
            'maintenance_request_id' => $mr->id,
            'cost'                   => 100.00,
            'store_id'               => $store->id,
            'company_id'             => $company->id,
            'date'                   => now()->toDateString(),
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        DB::table('invoice_card_materials')->insert([
            'invoice_card_id'        => $card->id,
            'maintenance_request_id' => $mr->id,
            'item_name'              => 'Part',
            'cost'                   => 50.00,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $cost = $this->service->calculatePurchaseCost($mr->id);

        $this->assertEquals(150.00, $cost);
    }

    // ── Test: Equipment matchByName ───────────────────────────────────────────

    public function test_match_by_name_finds_equipment_case_insensitive(): void
    {
        $eq     = $this->makeEquipment(['name' => 'Walk-in Cooler']);
        $others = $this->makeEquipment(['name' => 'Others']);

        $match = Equipment::matchByName('walk-in cooler');

        $this->assertEquals($eq->id, $match);
    }

    public function test_match_by_name_falls_back_to_others_when_no_match(): void
    {
        $others = $this->makeEquipment(['name' => 'Others']);

        $match = Equipment::matchByName('Some Unknown Machine');

        $this->assertEquals($others->id, $match);
    }
}
