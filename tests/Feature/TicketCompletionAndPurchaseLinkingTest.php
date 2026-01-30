<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Store;
use App\Models\Clocking;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use Modules\Invoice\app\Models\InvoiceCard;
use App\Services\TicketCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ticket Completion and Admin Purchase Linking Testing
 * 
 * Tests ticket status updates and admin purchase linking including:
 * - Single task ticket completion
 * - Multiple task ticket completion
 * - Partial completion
 * - Status sync to Cognito
 * - Purchase linking (create, edit, view, filter)
 * - Edge cases and error handling
 * 
 * @group Feature: invoice-system-enhancements
 * @group Task: 9.4, 9.5
 */
class TicketCompletionAndPurchaseLinkingTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;
    private User $admin;
    private Store $store;
    private TicketCompletionService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->technician = User::factory()->create([
            'role' => 'user',
            'hourly_pay' => 25.00
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->store = Store::factory()->create();

        $this->ticketService = app(TicketCompletionService::class);
    }

    /**
     * Test: Single task ticket completion
     * 
     * Validates that ticket status updates to 'done' when single task completes
     */
    public function test_single_task_ticket_completes_automatically(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress',
            'assigned_to' => $this->technician->id
        ]);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        // Link card to maintenance request
        $card->maintenanceRequests()->attach($maintenanceRequest->id);

        // Complete the card
        $this->actingAs($this->technician);
        $this->post(route('invoice.cards.complete', $card), [
            'how_we_fixed_it' => 'Replaced broken part',
            'end_time' => now()
        ]);

        $maintenanceRequest->refresh();
        $this->assertEquals('done', $maintenanceRequest->status);
    }

    /**
     * Test: Multiple task ticket - partial completion
     * 
     * Validates that ticket remains 'in_progress' when only some tasks complete
     */
    public function test_multiple_task_ticket_partial_completion(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress',
            'assigned_to' => $this->technician->id
        ]);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        // Create two cards for same ticket
        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        // Link both cards to same maintenance request
        $card1->maintenanceRequests()->attach($maintenanceRequest->id);
        $card2->maintenanceRequests()->attach($maintenanceRequest->id);

        // Complete only first card
        $this->actingAs($this->technician);
        $this->post(route('invoice.cards.complete', $card1), [
            'how_we_fixed_it' => 'Fixed part 1',
            'end_time' => now()
        ]);

        $maintenanceRequest->refresh();
        $this->assertEquals('in_progress', $maintenanceRequest->status);
    }

    /**
     * Test: Multiple task ticket - all tasks complete
     * 
     * Validates that ticket updates to 'done' when all tasks complete
     */
    public function test_multiple_task_ticket_all_tasks_complete(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress',
            'assigned_to' => $this->technician->id
        ]);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'completed'
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $card1->maintenanceRequests()->attach($maintenanceRequest->id);
        $card2->maintenanceRequests()->attach($maintenanceRequest->id);

        // Complete second card
        $this->actingAs($this->technician);
        $this->post(route('invoice.cards.complete', $card2), [
            'how_we_fixed_it' => 'Fixed part 2',
            'end_time' => now()
        ]);

        $maintenanceRequest->refresh();
        $this->assertEquals('done', $maintenanceRequest->status);
    }

    /**
     * Test: Cognito sync on ticket completion
     * 
     * Validates that completed ticket syncs to Cognito Forms
     */
    public function test_completed_ticket_syncs_to_cognito(): void
    {
        $this->markTestSkipped('Requires Cognito API mock');

        // This test would verify:
        // - CognitoFormsService::updateEntry() is called
        // - Correct data is sent to Cognito
        // - Sync status is recorded
    }

    /**
     * Test: Cognito API failure handling
     * 
     * Validates that ticket completion succeeds even if Cognito sync fails
     */
    public function test_ticket_completion_succeeds_despite_cognito_failure(): void
    {
        $this->markTestSkipped('Requires Cognito API mock');

        // This test would verify:
        // - Card completes successfully
        // - Ticket status updates
        // - not_in_cognito flag is set
        // - Error is logged
    }

    /**
     * Test: Network timeout handling
     * 
     * Validates that system handles network timeouts gracefully
     */
    public function test_network_timeout_handled_gracefully(): void
    {
        $this->markTestSkipped('Requires network mock');

        // This test would verify:
        // - Timeout doesn't block completion
        // - Retry is scheduled
        // - User sees success message
    }

    /**
     * Test: Invalid ticket ID handling
     * 
     * Validates that system handles invalid ticket IDs
     */
    public function test_invalid_ticket_id_handled(): void
    {
        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        // Link to non-existent ticket
        $card->maintenanceRequests()->attach(99999);

        $this->actingAs($this->technician);
        $response = $this->post(route('invoice.cards.complete', $card), [
            'how_we_fixed_it' => 'Fixed it',
            'end_time' => now()
        ]);

        // Should complete card but log error for invalid ticket
        $response->assertRedirect();
        $card->refresh();
        $this->assertEquals('completed', $card->status);
    }

    /**
     * Test: Already completed ticket handling
     * 
     * Validates that system handles already completed tickets
     */
    public function test_already_completed_ticket_handled(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'done',
            'assigned_to' => $this->technician->id
        ]);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $card->maintenanceRequests()->attach($maintenanceRequest->id);

        $this->actingAs($this->technician);
        $response = $this->post(route('invoice.cards.complete', $card), [
            'how_we_fixed_it' => 'Fixed it',
            'end_time' => now()
        ]);

        // Should complete card without error
        $response->assertRedirect();
        $card->refresh();
        $this->assertEquals('completed', $card->status);
    }

    /**
     * Test: Create purchase with ticket link
     * 
     * Validates that admin can create purchase linked to ticket
     */
    public function test_admin_can_create_purchase_with_ticket_link(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $this->actingAs($this->admin);
        $response = $this->post(route('admin.payments.store'), [
            'store_id' => $this->store->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest->id,
            'amount' => 150.00,
            'description' => 'Replacement compressor',
            'receipt' => \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg')
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'maintenance_request_id' => $maintenanceRequest->id,
            'amount' => 150.00,
            'description' => 'Replacement compressor'
        ]);
    }

    /**
     * Test: Create purchase without ticket link
     * 
     * Validates that admin can create purchase without linking to ticket
     */
    public function test_admin_can_create_purchase_without_ticket_link(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.payments.store'), [
            'store_id' => $this->store->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => null,
            'amount' => 75.00,
            'description' => 'General supplies',
            'receipt' => \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg')
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'maintenance_request_id' => null,
            'amount' => 75.00,
            'description' => 'General supplies'
        ]);
    }

    /**
     * Test: Edit purchase to add ticket link
     * 
     * Validates that admin can edit purchase to add ticket link
     */
    public function test_admin_can_edit_purchase_to_add_ticket_link(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'store_id' => $this->store->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => null,
            'amount' => 100.00
        ]);

        $this->actingAs($this->admin);
        $response = $this->put(route('admin.payments.update', $payment), [
            'store_id' => $this->store->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest->id,
            'amount' => 100.00,
            'description' => $payment->description
        ]);

        $response->assertRedirect();
        $payment->refresh();
        $this->assertEquals($maintenanceRequest->id, $payment->maintenance_request_id);
    }

    /**
     * Test: Edit purchase to remove ticket link
     * 
     * Validates that admin can edit purchase to remove ticket link
     */
    public function test_admin_can_edit_purchase_to_remove_ticket_link(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'store_id' => $this->store->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest->id,
            'amount' => 100.00
        ]);

        $this->actingAs($this->admin);
        $response = $this->put(route('admin.payments.update', $payment), [
            'store_id' => $this->store->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => null,
            'amount' => 100.00,
            'description' => $payment->description
        ]);

        $response->assertRedirect();
        $payment->refresh();
        $this->assertNull($payment->maintenance_request_id);
    }

    /**
     * Test: View purchases in invoice filtered by ticket
     * 
     * Validates that invoice shows only purchases for specific ticket
     */
    public function test_invoice_shows_purchases_filtered_by_ticket(): void
    {
        $maintenanceRequest1 = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $maintenanceRequest2 = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        // Purchase for ticket 1
        $payment1 = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest1->id,
            'description' => 'Purchase for ticket 1',
            'amount' => 100.00
        ]);

        // Purchase for ticket 2
        $payment2 = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest2->id,
            'description' => 'Purchase for ticket 2',
            'amount' => 200.00
        ]);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => now()
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'completed'
        ]);

        $card->maintenanceRequests()->attach($maintenanceRequest1->id);

        $this->actingAs($this->technician);
        $response = $this->get(route('invoice.cards.show', $card));

        $response->assertOk();
        $response->assertSee('Purchase for ticket 1');
        $response->assertSee('$100.00');
        $response->assertDontSee('Purchase for ticket 2');
    }

    /**
     * Test: Invalid ticket ID when creating purchase
     * 
     * Validates that system rejects invalid ticket IDs
     */
    public function test_invalid_ticket_id_rejected_when_creating_purchase(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.payments.store'), [
            'store_id' => $this->store->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => 99999, // Non-existent
            'amount' => 150.00,
            'description' => 'Test purchase',
            'receipt' => \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg')
        ]);

        $response->assertSessionHasErrors('maintenance_request_id');
    }

    /**
     * Test: Deleted ticket handling
     * 
     * Validates that purchases linked to deleted tickets are handled
     */
    public function test_deleted_ticket_handling(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest->id,
            'amount' => 100.00
        ]);

        // Delete the maintenance request
        $maintenanceRequest->delete();

        // Payment should still exist but link is broken
        $payment->refresh();
        $this->assertNotNull($payment);
        
        // Viewing invoice should handle missing ticket gracefully
        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => now()
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'completed'
        ]);

        $this->actingAs($this->technician);
        $response = $this->get(route('invoice.cards.show', $card));

        $response->assertOk(); // Should not error
    }

    /**
     * Test: Multiple purchases per ticket
     * 
     * Validates that multiple purchases can be linked to same ticket
     */
    public function test_multiple_purchases_per_ticket(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $payment1 = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest->id,
            'description' => 'Part 1',
            'amount' => 100.00
        ]);

        $payment2 = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest->id,
            'description' => 'Part 2',
            'amount' => 150.00
        ]);

        $payment3 = Payment::factory()->create([
            'user_id' => $this->admin->id,
            'is_admin_equipment' => true,
            'maintenance_request_id' => $maintenanceRequest->id,
            'description' => 'Part 3',
            'amount' => 75.00
        ]);

        $purchases = Payment::where('maintenance_request_id', $maintenanceRequest->id)->get();

        $this->assertCount(3, $purchases);
        $this->assertEquals(325.00, $purchases->sum('amount'));
    }

    /**
     * Test: Ticket completion service check
     * 
     * Unit test for TicketCompletionService::checkTicketCompletion()
     */
    public function test_ticket_completion_service_check(): void
    {
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'completed'
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'status' => 'in_progress'
        ]);

        $card1->maintenanceRequests()->attach($maintenanceRequest->id);
        $card2->maintenanceRequests()->attach($maintenanceRequest->id);

        // Should return false (not all complete)
        $isComplete = $this->ticketService->checkTicketCompletion($maintenanceRequest->id);
        $this->assertFalse($isComplete);

        // Complete second card
        $card2->update(['status' => 'completed']);

        // Should return true (all complete)
        $isComplete = $this->ticketService->checkTicketCompletion($maintenanceRequest->id);
        $this->assertTrue($isComplete);
    }
}
