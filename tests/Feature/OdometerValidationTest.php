<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Store;
use App\Models\Clocking;
use Modules\Invoice\app\Models\InvoiceCard;
use Modules\Invoice\app\Services\OdometerCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Odometer Validation Testing
 * 
 * Tests validation rules for odometer readings including:
 * - Non-decreasing odometer
 * - Missing odometer entries
 * - Odometer rollover (999,999 → 0)
 * - Invalid odometer values
 * - Gaps in sequence
 * 
 * @group Feature: invoice-system-enhancements
 * @group Task: 9.2
 */
class OdometerValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;
    private Store $store;
    private OdometerCalculationService $odometerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->technician = User::factory()->create([
            'role' => 'user',
            'hourly_pay' => 25.00
        ]);

        $this->store = Store::factory()->create();

        $this->odometerService = app(OdometerCalculationService::class);
    }

    /**
     * Test: Non-decreasing odometer validation
     * 
     * Validates that odometer readings must be >= previous reading
     */
    public function test_odometer_must_not_decrease(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        // Try to create card with lower odometer
        $response = $this->post(route('invoice.cards.store'), [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'arrival_odometer' => 44999 // Less than miles_in
        ]);

        $response->assertSessionHasErrors('arrival_odometer');
        $this->assertDatabaseMissing('invoice_cards', [
            'clocking_id' => $clocking->id,
            'arrival_odometer' => 44999
        ]);
    }

    /**
     * Test: Missing odometer entries
     * 
     * Validates that odometer is required when using car
     */
    public function test_odometer_required_when_using_car(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        // Try to create card without odometer
        $response = $this->post(route('invoice.cards.store'), [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            // arrival_odometer missing
        ]);

        $response->assertSessionHasErrors('arrival_odometer');
    }

    /**
     * Test: Odometer not required when not using car
     * 
     * Validates that odometer is optional when using_car is false
     */
    public function test_odometer_not_required_when_not_using_car(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => false,
            'clock_out' => null
        ]);

        $response = $this->post(route('invoice.cards.store'), [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            // arrival_odometer not provided
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoice_cards', [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id
        ]);
    }

    /**
     * Test: Odometer rollover handling (999,999 → 0)
     * 
     * Validates that system handles odometer rollover correctly
     */
    public function test_odometer_rollover_handled_correctly(): void
    {
        $previousReading = 999990;
        $currentReading = 10; // Rolled over

        $distance = $this->odometerService->handleRollover($currentReading, $previousReading);

        // Distance should be: (1000000 - 999990) + 10 = 20
        $this->assertEquals(20, $distance);
    }

    /**
     * Test: Invalid odometer values (negative)
     * 
     * Validates that negative odometer values are rejected
     */
    public function test_negative_odometer_rejected(): void
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
            'store_id' => $this->store->id,
            'arrival_odometer' => -100
        ]);

        $response->assertSessionHasErrors('arrival_odometer');
    }

    /**
     * Test: Invalid odometer values (non-numeric)
     * 
     * Validates that non-numeric odometer values are rejected
     */
    public function test_non_numeric_odometer_rejected(): void
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
            'store_id' => $this->store->id,
            'arrival_odometer' => 'abc123'
        ]);

        $response->assertSessionHasErrors('arrival_odometer');
    }

    /**
     * Test: Gaps in sequence detection
     * 
     * Validates that large gaps in odometer readings trigger warnings
     */
    public function test_large_odometer_gap_triggers_warning(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        // Create first card
        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'arrival_odometer' => 45025
        ]);

        // Try to create second card with large gap (>500 miles)
        $response = $this->post(route('invoice.cards.store'), [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'arrival_odometer' => 45600 // 575 mile gap
        ]);

        // Should succeed but with warning
        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }

    /**
     * Test: Clock-out odometer must be >= clock-in
     * 
     * Validates that final odometer must be >= starting odometer
     */
    public function test_clock_out_odometer_must_be_greater_than_clock_in(): void
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
            'miles_out' => 44999, // Less than miles_in
            'image_out' => \Illuminate\Http\UploadedFile::fake()->image('test.jpg')
        ]);

        $response->assertSessionHasErrors('miles_out');
    }

    /**
     * Test: Validation error messages are clear and helpful
     * 
     * Validates that error messages provide clear guidance
     */
    public function test_validation_error_messages_are_helpful(): void
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
            'store_id' => $this->store->id,
            'arrival_odometer' => 44999
        ]);

        $response->assertSessionHasErrors('arrival_odometer');
        
        $errors = session('errors');
        $errorMessage = $errors->first('arrival_odometer');
        
        $this->assertStringContainsString('must be greater than or equal to', $errorMessage);
        $this->assertStringContainsString('45000', $errorMessage); // Shows previous reading
    }

    /**
     * Test: Odometer validation service validates correctly
     * 
     * Unit test for OdometerCalculationService validation
     */
    public function test_odometer_service_validates_readings(): void
    {
        // Valid reading
        $this->assertTrue(
            $this->odometerService->validateOdometerReading(45025, 45000)
        );

        // Invalid reading (decreasing)
        $this->assertFalse(
            $this->odometerService->validateOdometerReading(44999, 45000)
        );

        // Valid reading (equal)
        $this->assertTrue(
            $this->odometerService->validateOdometerReading(45000, 45000)
        );

        // Valid reading (rollover)
        $this->assertTrue(
            $this->odometerService->validateOdometerReading(10, 999990)
        );
    }

    /**
     * Test: Sequential odometer readings validation
     * 
     * Validates that multiple cards maintain proper sequence
     */
    public function test_sequential_odometer_readings_validated(): void
    {
        $this->actingAs($this->technician);

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        // Create first card
        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'arrival_odometer' => 45025
        ]);

        // Create second card with valid sequence
        $response = $this->post(route('invoice.cards.store'), [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'arrival_odometer' => 45050
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoice_cards', [
            'clocking_id' => $clocking->id,
            'arrival_odometer' => 45050
        ]);

        // Try to create third card with invalid sequence
        $response = $this->post(route('invoice.cards.store'), [
            'clocking_id' => $clocking->id,
            'store_id' => $this->store->id,
            'arrival_odometer' => 45040 // Less than previous
        ]);

        $response->assertSessionHasErrors('arrival_odometer');
    }
}
