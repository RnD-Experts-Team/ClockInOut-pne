<?php

namespace Database\Factories;

use App\Models\MaintenanceRequest;
use App\Models\Store;
use App\Models\UrgencyLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceRequestFactory extends Factory
{
    protected $model = MaintenanceRequest::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'equipment_with_issue' => $this->faker->randomElement(['Refrigerator', 'HVAC', 'Freezer', 'Lights', 'Door']),
            'description_of_issue' => $this->faker->sentence(),
            'status' => 'in_progress',
            'urgency_level_id' => 1, // Assuming urgency levels exist
            'basic_troubleshoot_done' => false,
            'request_date' => now(),
            'date_submitted' => now(),
            'entry_number' => $this->faker->unique()->numberBetween(1000, 9999),
            'assigned_to' => null,
            'due_date' => now()->addDays(3),
            'not_in_cognito' => false,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => User::factory(),
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
            'costs' => $this->faker->randomFloat(2, 50, 500),
            'how_we_fixed_it' => $this->faker->sentence(),
        ]);
    }
}
