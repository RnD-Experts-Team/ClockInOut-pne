<?php

namespace Modules\Invoice\Database\Factories;

use Modules\Invoice\Models\InvoiceCard;
use App\Models\Clocking;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceCardFactory extends Factory
{
    protected $model = InvoiceCard::class;

    public function definition(): array
    {
        $startTime = now()->subHours(4);
        $endTime = null;

        return [
            'clocking_id' => Clocking::factory(),
            'store_id' => Store::factory(),
            'user_id' => User::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'arrival_odometer' => $this->faker->numberBetween(40000, 60000),
            'calculated_miles' => null,
            'driving_time_hours' => null,
            'driving_time_payment' => 0,
            'allocated_return_miles' => 0,
            'total_miles' => 0,
            'mileage_payment' => 0,
            'labor_hours' => 0,
            'labor_cost' => 0,
            'materials_cost' => 0,
            'total_cost' => 0,
            'status' => 'in_progress',
            'notes' => null,
            'not_done_reason' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = $attributes['start_time'];
            $endTime = $startTime->copy()->addHours(2);
            $laborHours = 2;
            $laborCost = $laborHours * 25; // Assuming $25/hour

            return [
                'end_time' => $endTime,
                'labor_hours' => $laborHours,
                'labor_cost' => $laborCost,
                'total_cost' => $laborCost,
                'status' => 'completed',
                'notes' => $this->faker->sentence(),
            ];
        });
    }
}
