<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Store;
use App\Models\Clocking;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use Modules\Invoice\app\Models\InvoiceCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-End Workflow Testing for Invoice System Enhancements
 * 
 * @group Feature: invoice-system-enhancements
 * @group Task: 9.1
 */
class InvoiceSystemEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;
    private User $admin;
    private Store $store1;
    private Store $store2;
    private MaintenanceRequest $maintenanceRequest1;
    private MaintenanceRequest $maintenanceRequest2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->technician = User::factory()->create([
            'role' => 'user',
            'hourly_pay' => 25.00,
            'preferred_language' => 'en'
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);

        // Create test stores
        $this->store1 = Store::factory()->create([
            'name' => 'Test Store 1',
            'store_number' => 'ST001',
            'address' => '123 Main St'
        ]);

        $this->store2 = Store::factory()->create([
            'name' => 'Test Store 2',
            'store_number' => 'ST002',
            'address' => '456 Oak Ave'
        ]);

        // Create test maintenance requests
        $this->maintenanceRequest1 = MaintenanceRequest::factory()->create([
            'store_id' => $this->store1->id,
            'equipment_with_issue' => 'Refrigerator',
            'issue_description' => 'Not cooling properly',
            'status' => 'in_progress',
            'assigned_to' => $this->technician->id
        ]);

        $this->maintenanceRequest2 = MaintenanceRequest::factory()->create([
            'store_id' => $this->store2->id,
            'equipment_with_issue' => 'HVAC System',
            'issue_description' => 'Making loud noise',
            'status' => 'in_progress',
            'assigned_to' => $this->technician->id
        ]);
    }

    /**
     * Test 1: Clock-in with odometer
     * 
     * Validates that technician can clock in with starting odometer reading
     */
    public function test_technician_can_clock_in_with_odometer(): void
    {
        $this->actingAs($this->technician);

        $response = $this->post(route('clocking.clock-in'), [
            'using_car' => true,
            'miles_in' => 45000,
            'image_in' => $this->createTestImage()
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $clocking = Clocking::where('user_id', $this->technician->id)
            ->whereNull('clock_out')
            ->first();

        $this->assertNotNull($clocking);
        $this->assertEquals(45000, $clocking->miles_in);
        $this->assertTrue($clocking->using_car);
    }

    /**
     * Test 2: View assigned stores
     * 
     * Validates that technician can see stores with assigned maintenance requests
     */
    public function test_technician_can_view_assigned_stores(): void
    {
        $this->actingAs($this->technician);

        // Create active clocking session
        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $response = $this->get(route('clocking.index'));

        $response->assertOk();
        $response->assertSee('Your Assigned Tasks Today');
        $response->assertSee($this->store1->name);
        $response->assertSee($this->store2->name);
    }

    /**
     * Test 3: Arrive at store and capture odometer
     * 
     * Validates that technician can create invoice card with arrival odometer
     */
    public function test_technician_can_arrive_at_store_with_odometer(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $response = $this->post(route('invoice.cards.store'), [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'start_time' => now()
        ]);

        $response->assertRedirect();

        $card = InvoiceCard::where('clocking_id', $clocking->id)
            ->where('store_id', $this->store1->id)
            ->first();

        $this->assertNotNull($card);
        $this->assertEquals(45025, $card->arrival_odometer);
        $this->assertEquals(25, $card->calculated_miles); // 45025 - 45000
        $this->assertNotNull($card->driving_time_hours);
        $this->assertNotNull($card->driving_time_payment);
    }

    /**
     * Test 4: Select task from dropdown
     * 
     * Validates that technician can select maintenance request from dropdown
     */
    public function test_technician_can_select_task_from_dropdown(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025
        ]);

        $response = $this->get(route('invoice.cards.show', $card));

        $response->assertOk();
        $response->assertSee('Select Task to Work On');
        $response->assertSee($this->maintenanceRequest1->equipment_with_issue);
        $this->assertStringNotContainsString('done', $response->content());
    }

    /**
     * Test 5: View admin purchases for task
     * 
     * Validates that admin purchases linked to task are displayed
     */
    public function test_technician_can_view_admin_purchases_for_task(): void
    {
        $this->actingAs($this->technician);

        // Create admin purchase linked to maintenance request
        $adminPurchase = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $this->maintenanceRequest1->id,
            'amount' => 150.00,
            'description' => 'Refrigerator compressor'
        ]);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025
        ]);

        $response = $this->get(route('invoice.cards.show', $card));

        $response->assertOk();
        $response->assertSee('Admin Purchases for This Task');
        $response->assertSee('Refrigerator compressor');
        $response->assertSee('$150.00');
    }

    /**
     * Test 6: Add materials
     * 
     * Validates that technician can add materials to invoice card
     */
    public function test_technician_can_add_materials(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025
        ]);

        $response = $this->post(route('invoice.materials.store'), [
            'invoice_card_id' => $card->id,
            'description' => 'Refrigerant R-134a',
            'cost' => 45.00,
            'receipt' => $this->createTestImage()
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_card_id' => $card->id,
            'description' => 'Refrigerant R-134a',
            'amount' => 45.00
        ]);
    }

    /**
     * Test 7: Complete work
     * 
     * Validates that technician can mark work as complete
     */
    public function test_technician_can_complete_work(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'status' => 'in_progress'
        ]);

        $response = $this->post(route('invoice.cards.complete', $card), [
            'how_we_fixed_it' => 'Replaced compressor and recharged refrigerant',
            'end_time' => now()
        ]);

        $response->assertRedirect();

        $card->refresh();
        $this->assertEquals('completed', $card->status);
        $this->assertNotNull($card->end_time);
    }

    /**
     * Test 8: Verify ticket status updated
     * 
     * Validates that maintenance request status is updated when all tasks complete
     */
    public function test_ticket_status_updates_when_all_tasks_complete(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'status' => 'in_progress'
        ]);

        // Link card to maintenance request
        $card->maintenanceRequests()->attach($this->maintenanceRequest1->id);

        // Complete the card
        $this->post(route('invoice.cards.complete', $card), [
            'how_we_fixed_it' => 'Replaced compressor',
            'end_time' => now()
        ]);

        $this->maintenanceRequest1->refresh();
        $this->assertEquals('done', $this->maintenanceRequest1->status);
    }

    /**
     * Test 9: Verify Cognito synced
     * 
     * Validates that completed work is synced to Cognito Forms
     */
    public function test_completed_work_syncs_to_cognito(): void
    {
        $this->markTestSkipped('Requires Cognito API integration');
        
        // This test would verify:
        // - CognitoFormsService is called
        // - Request data is properly formatted
        // - Sync status is recorded
    }

    /**
     * Test 10: Move to next store
     * 
     * Validates that technician can create card for second store
     */
    public function test_technician_can_move_to_next_store(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        // First store card
        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'status' => 'completed'
        ]);

        // Second store card
        $response = $this->post(route('invoice.cards.store'), [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store2->id,
            'arrival_odometer' => 45050
        ]);

        $response->assertRedirect();

        $card2 = InvoiceCard::where('clocking_id', $clocking->id)
            ->where('store_id', $this->store2->id)
            ->first();

        $this->assertNotNull($card2);
        $this->assertEquals(45050, $card2->arrival_odometer);
        $this->assertEquals(25, $card2->calculated_miles); // 45050 - 45025
    }

    /**
     * Test 11: Clock-out
     * 
     * Validates that technician can clock out with final odometer
     */
    public function test_technician_can_clock_out(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $response = $this->post(route('clocking.clock-out'), [
            'clocking_id' => $clocking->id,
            'miles_out' => 45075,
            'image_out' => $this->createTestImage()
        ]);

        $response->assertRedirect();

        $clocking->refresh();
        $this->assertNotNull($clocking->clock_out);
        $this->assertEquals(45075, $clocking->miles_out);
    }

    /**
     * Test 12: Verify final segment distributed
     * 
     * Validates that final segment miles are distributed by percentage
     */
    public function test_final_segment_distributed_by_percentage(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'miles_out' => 45100,
            'clock_out' => now()
        ]);

        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'calculated_miles' => 25,
            'status' => 'completed'
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store2->id,
            'arrival_odometer' => 45075,
            'calculated_miles' => 50,
            'status' => 'completed'
        ]);

        // Trigger final segment distribution
        app(\Modules\Invoice\app\Services\MileageDistributionService::class)
            ->distributeReturnMiles($clocking->id);

        $card1->refresh();
        $card2->refresh();

        // Final segment = 45100 - 45075 = 25 miles
        // Card1 percentage = 25 / (25 + 50) = 33.33%
        // Card2 percentage = 50 / (25 + 50) = 66.67%
        // Card1 gets: 25 * 0.3333 = 8.33 miles
        // Card2 gets: 25 * 0.6667 = 16.67 miles

        $this->assertGreaterThan(0, $card1->allocated_return_miles);
        $this->assertGreaterThan(0, $card2->allocated_return_miles);
        $this->assertEqualsWithDelta(
            25,
            $card1->allocated_return_miles + $card2->allocated_return_miles,
            0.1
        );
    }

    /**
     * Test 13: Generate invoice
     * 
     * Validates that invoice can be generated with all data
     */
    public function test_invoice_can_be_generated(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'miles_out' => 45100,
            'clock_out' => now()
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'calculated_miles' => 25,
            'driving_time_hours' => 0.5,
            'driving_time_payment' => 12.50,
            'status' => 'completed'
        ]);

        $response = $this->get(route('invoice.generate', $clocking));

        $response->assertOk();
        $response->assertSee('Invoice');
        $response->assertSee($this->technician->name);
        $response->assertSee($this->store1->name);
    }

    /**
     * Test 14: Verify invoice format correct
     * 
     * Validates that invoice displays all cost breakdowns correctly
     */
    public function test_invoice_format_displays_all_costs(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'miles_out' => 45100,
            'clock_out' => now()
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'calculated_miles' => 25,
            'driving_time_hours' => 0.5,
            'driving_time_payment' => 12.50,
            'mileage_payment' => 15.00,
            'working_time_payment' => 50.00,
            'status' => 'completed'
        ]);

        $response = $this->get(route('invoice.generate', $clocking));

        $response->assertOk();
        $response->assertSee('Mileage and Driving Payment');
        $response->assertSee('Working Time Payment');
        $response->assertSee('Materials');
        $response->assertSee('$12.50'); // Driving time payment
        $response->assertSee('$15.00'); // Mileage payment
        $response->assertSee('$50.00'); // Working time payment
    }

    /**
     * Helper method to create test image
     */
    private function createTestImage()
    {
        return \Illuminate\Http\UploadedFile::fake()->image('test.jpg');
    }
}
