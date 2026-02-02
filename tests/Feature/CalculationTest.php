<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Store;
use App\Models\Clocking;
use Modules\Invoice\app\Models\InvoiceCard;
use Modules\Invoice\app\Services\OdometerCalculationService;
use Modules\Invoice\app\Services\MileageDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Calculation Testing
 * 
 * Tests all calculation logic including:
 * - Distance calculation (first store, subsequent stores, final segment)
 * - Driving time calculation
 * - Payment calculation (mileage, driving time, working time, materials, admin purchases)
 * - Percentage distribution
 * 
 * @group Feature: invoice-system-enhancements
 * @group Task: 9.3
 */
class CalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;
    private Store $store1;
    private Store $store2;
    private Store $store3;
    private OdometerCalculationService $odometerService;
    private MileageDistributionService $mileageService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->technician = User::factory()->create([
            'role' => 'user',
            'hourly_pay' => 25.00
        ]);

        $this->store1 = Store::factory()->create(['name' => 'Store 1']);
        $this->store2 = Store::factory()->create(['name' => 'Store 2']);
        $this->store3 = Store::factory()->create(['name' => 'Store 3']);

        $this->odometerService = app(OdometerCalculationService::class);
        $this->mileageService = app(MileageDistributionService::class);
    }

    /**
     * Test: Distance calculation for first store
     * 
     * Validates: distance = arrival_odometer - miles_in
     */
    public function test_distance_calculation_for_first_store(): void
    {
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

        $distance = $this->odometerService->calculateDistance($card);

        $this->assertEquals(25, $distance);
        $this->assertEquals(25, $card->calculated_miles);
    }

    /**
     * Test: Distance calculation for subsequent stores
     * 
     * Validates: distance = current_arrival - previous_arrival
     */
    public function test_distance_calculation_for_subsequent_stores(): void
    {
        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_out' => null
        ]);

        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'calculated_miles' => 25
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store2->id,
            'arrival_odometer' => 45075
        ]);

        $distance = $this->odometerService->calculateDistance($card2);

        $this->assertEquals(50, $distance); // 45075 - 45025
        $this->assertEquals(50, $card2->calculated_miles);
    }

    /**
     * Test: Final segment calculation
     * 
     * Validates: final_segment = miles_out - last_arrival_odometer
     */
    public function test_final_segment_calculation(): void
    {
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
            'arrival_odometer' => 45075,
            'calculated_miles' => 75
        ]);

        $finalSegment = $this->mileageService->calculateFinalSegment($clocking->id);

        $this->assertEquals(25, $finalSegment); // 45100 - 45075
    }

    /**
     * Test: Percentage distribution calculation
     * 
     * Validates: percentage = card_miles / total_miles
     */
    public function test_percentage_distribution_calculation(): void
    {
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
            'calculated_miles' => 25
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store2->id,
            'arrival_odometer' => 45075,
            'calculated_miles' => 50
        ]);

        $percentages = $this->mileageService->calculatePercentages($clocking->id);

        // Total driven = 25 + 50 = 75
        // Card1 percentage = 25/75 = 0.3333
        // Card2 percentage = 50/75 = 0.6667
        $this->assertEqualsWithDelta(0.3333, $percentages[$card1->id], 0.01);
        $this->assertEqualsWithDelta(0.6667, $percentages[$card2->id], 0.01);
    }

    /**
     * Test: Final segment distribution by percentage
     * 
     * Validates: allocated_miles = final_segment × percentage
     */
    public function test_final_segment_distributed_by_percentage(): void
    {
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
            'calculated_miles' => 25
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store2->id,
            'arrival_odometer' => 45075,
            'calculated_miles' => 50
        ]);

        $this->mileageService->distributeReturnMiles($clocking->id);

        $card1->refresh();
        $card2->refresh();

        // Final segment = 25 miles
        // Card1 gets: 25 × 0.3333 = 8.33
        // Card2 gets: 25 × 0.6667 = 16.67
        $this->assertEqualsWithDelta(8.33, $card1->allocated_return_miles, 0.5);
        $this->assertEqualsWithDelta(16.67, $card2->allocated_return_miles, 0.5);

        // Total should equal final segment
        $total = $card1->allocated_return_miles + $card2->allocated_return_miles;
        $this->assertEqualsWithDelta(25, $total, 0.1);
    }

    /**
     * Test: Driving time calculation for first store
     * 
     * Validates: driving_time = (arrival_time - clock_in) / 3600
     */
    public function test_driving_time_calculation_for_first_store(): void
    {
        $clockIn = now()->subHours(2);
        $arrivalTime = now()->subHours(1)->subMinutes(30); // 30 minutes driving

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_in' => $clockIn,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'start_time' => $arrivalTime
        ]);

        $drivingTime = $this->odometerService->calculateDrivingTime($card);

        $this->assertEqualsWithDelta(0.5, $drivingTime, 0.01); // 30 minutes = 0.5 hours
        $this->assertEqualsWithDelta(0.5, $card->driving_time_hours, 0.01);
    }

    /**
     * Test: Driving time calculation for subsequent stores
     * 
     * Validates: driving_time = (current_arrival - previous_end) / 3600
     */
    public function test_driving_time_calculation_for_subsequent_stores(): void
    {
        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_in' => now()->subHours(3),
            'clock_out' => null
        ]);

        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'start_time' => now()->subHours(2)->subMinutes(30),
            'end_time' => now()->subHours(2)
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store2->id,
            'arrival_odometer' => 45075,
            'start_time' => now()->subHours(1)->subMinutes(15) // 45 minutes driving
        ]);

        $drivingTime = $this->odometerService->calculateDrivingTime($card2);

        $this->assertEqualsWithDelta(0.75, $drivingTime, 0.01); // 45 minutes = 0.75 hours
    }

    /**
     * Test: Driving time payment calculation
     * 
     * Validates: payment = driving_time_hours × hourly_pay
     */
    public function test_driving_time_payment_calculation(): void
    {
        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_in' => now()->subHours(2),
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'start_time' => now()->subHours(1)->subMinutes(30),
            'driving_time_hours' => 0.5
        ]);

        $payment = $this->odometerService->calculateDrivingPayment($card);

        // 0.5 hours × $25/hour = $12.50
        $this->assertEquals(12.50, $payment);
        $this->assertEquals(12.50, $card->driving_time_payment);
    }

    /**
     * Test: Mileage payment calculation
     * 
     * Validates: payment = total_miles × mileage_rate
     */
    public function test_mileage_payment_calculation(): void
    {
        $mileageRate = 0.67; // $0.67 per mile

        $card = InvoiceCard::factory()->create([
            'calculated_miles' => 25,
            'allocated_return_miles' => 8.33,
            'mileage_rate' => $mileageRate
        ]);

        $totalMiles = $card->calculated_miles + $card->allocated_return_miles;
        $expectedPayment = $totalMiles * $mileageRate;

        $this->assertEqualsWithDelta(22.33, $expectedPayment, 0.5);
    }

    /**
     * Test: Working time payment calculation
     * 
     * Validates: payment = working_hours × hourly_pay
     */
    public function test_working_time_payment_calculation(): void
    {
        $startTime = now()->subHours(2);
        $endTime = now();

        $card = InvoiceCard::factory()->create([
            'user_id' => $this->technician->id,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);

        $workingHours = $endTime->diffInHours($startTime);
        $expectedPayment = $workingHours * $this->technician->hourly_pay;

        $this->assertEquals(2, $workingHours);
        $this->assertEquals(50.00, $expectedPayment); // 2 hours × $25/hour
    }

    /**
     * Test: Total cost calculation with all components
     * 
     * Validates: total = mileage + driving_time + working_time + materials + admin_purchases
     */
    public function test_total_cost_calculation_with_all_components(): void
    {
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
            'calculated_miles' => 25,
            'allocated_return_miles' => 8.33,
            'mileage_payment' => 22.33,
            'driving_time_hours' => 0.5,
            'driving_time_payment' => 12.50,
            'working_time_payment' => 50.00
        ]);

        // Add materials
        $materialsCost = 45.00;

        // Add admin purchases
        $adminPurchasesCost = 150.00;

        $totalCost = $card->mileage_payment 
                   + $card->driving_time_payment 
                   + $card->working_time_payment 
                   + $materialsCost 
                   + $adminPurchasesCost;

        $this->assertEqualsWithDelta(279.83, $totalCost, 0.5);
    }

    /**
     * Test: Edge case - same time driving (instant arrival)
     * 
     * Validates: driving_time = 0 when arrival_time = previous_time
     */
    public function test_zero_driving_time_for_instant_arrival(): void
    {
        $time = now();

        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_in' => $time,
            'clock_out' => null
        ]);

        $card = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45000,
            'start_time' => $time
        ]);

        $drivingTime = $this->odometerService->calculateDrivingTime($card);

        $this->assertEquals(0, $drivingTime);
        $this->assertEquals(0, $card->driving_time_payment);
    }

    /**
     * Test: Edge case - long gap between stores
     * 
     * Validates: system handles long time gaps correctly
     */
    public function test_long_time_gap_between_stores(): void
    {
        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'clock_in' => now()->subHours(10),
            'clock_out' => null
        ]);

        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45025,
            'start_time' => now()->subHours(9)->subMinutes(30),
            'end_time' => now()->subHours(5) // 4.5 hour work
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store2->id,
            'arrival_odometer' => 45075,
            'start_time' => now()->subHours(1) // 4 hour gap
        ]);

        $drivingTime = $this->odometerService->calculateDrivingTime($card2);

        $this->assertEqualsWithDelta(4.0, $drivingTime, 0.1);
    }

    /**
     * Test: Calculation accuracy with multiple stores
     * 
     * Validates: all calculations remain accurate across multiple stores
     */
    public function test_calculation_accuracy_with_multiple_stores(): void
    {
        $clocking = Clocking::factory()->create([
            'user_id' => $this->technician->id,
            'using_car' => true,
            'miles_in' => 45000,
            'miles_out' => 45150,
            'clock_in' => now()->subHours(8),
            'clock_out' => now()
        ]);

        $card1 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store1->id,
            'arrival_odometer' => 45030,
            'calculated_miles' => 30
        ]);

        $card2 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store2->id,
            'arrival_odometer' => 45080,
            'calculated_miles' => 50
        ]);

        $card3 = InvoiceCard::factory()->create([
            'clocking_id' => $clocking->id,
            'store_id' => $this->store3->id,
            'arrival_odometer' => 45130,
            'calculated_miles' => 50
        ]);

        $this->mileageService->distributeReturnMiles($clocking->id);

        $card1->refresh();
        $card2->refresh();
        $card3->refresh();

        // Total driven miles = 30 + 50 + 50 = 130
        // Final segment = 45150 - 45130 = 20
        // Total allocated should equal final segment
        $totalAllocated = $card1->allocated_return_miles 
                        + $card2->allocated_return_miles 
                        + $card3->allocated_return_miles;

        $this->assertEqualsWithDelta(20, $totalAllocated, 0.1);

        // Each card's total miles should be calculated + allocated
        $this->assertEqualsWithDelta(
            30 + $card1->allocated_return_miles,
            $card1->total_miles,
            0.1
        );
    }
}
