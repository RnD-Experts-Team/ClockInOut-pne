<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTrackerTest extends TestCase
{
    use RefreshDatabase;

    // ── Auth helpers ──────────────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function tech(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_equipment_index(): void
    {
        $this->actingAs($this->admin());

        Equipment::create(['name' => 'Freezer', 'is_active' => true]);

        $this->get(route('admin.equipment.index'))
            ->assertOk()
            ->assertSee('Freezer');
    }

    public function test_non_admin_cannot_see_equipment_index(): void
    {
        $this->actingAs($this->tech());

        // RoleMiddleware redirects (302) non-admin users rather than returning 403
        $this->get(route('admin.equipment.index'))
            ->assertRedirect();
    }

    public function test_index_filters_by_store(): void
    {
        $this->actingAs($this->admin());

        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        Equipment::create(['name' => 'Fridge A', 'store_id' => $store1->id, 'is_active' => true]);
        Equipment::create(['name' => 'Fridge B', 'store_id' => $store2->id, 'is_active' => true]);

        $resp = $this->get(route('admin.equipment.index', ['store' => $store1->id]));
        $resp->assertOk()
             ->assertSee('Fridge A')
             ->assertDontSee('Fridge B');
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_admin_can_load_create_form(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.equipment.create'))
            ->assertOk();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_admin_can_create_equipment(): void
    {
        $this->actingAs($this->admin());

        $store = Store::factory()->create();

        $this->post(route('admin.equipment.store'), [
            'name'          => 'Walk-in Cooler',
            'store_id'      => $store->id,
            'type'          => 'Refrigeration',
            'serial_number' => 'SN-1234',
            'model'         => 'CoolMax 5000',
            'notes'         => 'Annual check done.',
            'is_active'     => true,
        ])->assertRedirect(route('admin.equipment.index'));

        $this->assertDatabaseHas('equipment', [
            'name'     => 'Walk-in Cooler',
            'store_id' => $store->id,
            'type'     => 'Refrigeration',
        ]);
    }

    public function test_store_validates_required_name(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.equipment.store'), [])
            ->assertSessionHasErrors('name');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_admin_can_view_equipment_show(): void
    {
        $this->actingAs($this->admin());

        $item = Equipment::create(['name' => 'HVAC Unit', 'is_active' => true]);

        $this->get(route('admin.equipment.show', $item->id))
            ->assertOk()
            ->assertSee('HVAC Unit');
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function test_admin_can_update_equipment(): void
    {
        $this->actingAs($this->admin());

        $item = Equipment::create(['name' => 'Ice Machine', 'is_active' => true]);

        $this->put(route('admin.equipment.update', $item->id), [
            'name'      => 'Ice Machine Pro',
            'is_active' => true,
        ])->assertRedirect(route('admin.equipment.show', $item->id));

        $this->assertDatabaseHas('equipment', [
            'id'   => $item->id,
            'name' => 'Ice Machine Pro',
        ]);
    }

    // ── Deactivate (destroy) ──────────────────────────────────────────────────

    public function test_admin_deactivate_sets_is_active_false(): void
    {
        $this->actingAs($this->admin());

        $item = Equipment::create(['name' => 'Freezer', 'is_active' => true]);

        $this->delete(route('admin.equipment.destroy', $item->id))
            ->assertRedirect(route('admin.equipment.index'));

        $this->assertDatabaseHas('equipment', [
            'id'        => $item->id,
            'is_active' => false,
        ]);
    }

    // ── Re-assign MR ──────────────────────────────────────────────────────────

    public function test_admin_can_reassign_mr_to_different_equipment(): void
    {
        $this->actingAs($this->admin());

        $store  = Store::factory()->create();
        $eq1    = Equipment::create(['name' => 'Freezer', 'is_active' => true]);
        $eq2    = Equipment::create(['name' => 'HVAC',    'is_active' => true]);
        $mr     = MaintenanceRequest::factory()->create([
            'store_id'     => $store->id,
            'equipment_id' => $eq1->id,
        ]);

        $this->patch(route('admin.equipment.mr.reassign', $mr->id), [
            'equipment_id' => $eq2->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('maintenance_requests', [
            'id'           => $mr->id,
            'equipment_id' => $eq2->id,
        ]);
    }

    // ── CSV Export ────────────────────────────────────────────────────────────

    public function test_export_returns_csv_download(): void
    {
        $this->actingAs($this->admin());

        Equipment::create(['name' => 'Grill', 'is_active' => true]);

        $response = $this->get(route('admin.equipment.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_export_detail_returns_csv_download(): void
    {
        $this->actingAs($this->admin());

        $item = Equipment::create(['name' => 'Grill', 'is_active' => true]);

        $response = $this->get(route('admin.equipment.export-detail', $item->id));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    // ── Auto-link on MR creation ──────────────────────────────────────────────

    public function test_new_mr_auto_links_to_matching_equipment(): void
    {
        $store = Store::factory()->create();
        Equipment::create(['name' => 'Ice Machine', 'is_active' => true]);
        Equipment::create(['name' => 'Others',      'is_active' => true]);

        $mr = MaintenanceRequest::create([
            'store_id'             => $store->id,
            'equipment_with_issue' => 'Ice Machine',
            'description_of_issue' => 'Not making ice.',
            'status'               => 'in_progress',
            'request_date'         => now(),
            'date_submitted'       => now(),
            'not_in_cognito'       => false,
        ]);

        $expectedId = Equipment::where('name', 'Ice Machine')->first()->id;
        $this->assertEquals($expectedId, $mr->fresh()->equipment_id);
    }

    public function test_new_mr_falls_back_to_others_when_no_match(): void
    {
        $store  = Store::factory()->create();
        $others = Equipment::create(['name' => 'Others', 'is_active' => true]);

        $mr = MaintenanceRequest::create([
            'store_id'             => $store->id,
            'equipment_with_issue' => 'Flux Capacitor',
            'description_of_issue' => 'Broken.',
            'status'               => 'in_progress',
            'request_date'         => now(),
            'date_submitted'       => now(),
            'not_in_cognito'       => false,
        ]);

        $this->assertEquals($others->id, $mr->fresh()->equipment_id);
    }
}
