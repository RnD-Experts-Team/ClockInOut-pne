<?php

namespace Database\Factories;

use App\Models\Clocking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClockingFactory extends Factory
{
    protected $model = Clocking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clock_in' => now()->subHours(8),
            'clock_out' => null,
            'using_car' => true,
            'miles_in' => $this->faker->numberBetween(40000, 60000),
            'miles_out' => null,
            'image_in' => 'clockings/test_image_in.jpg',
            'image_out' => null,
        ];
    }

    public function clockedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_out' => now(),
            'miles_out' => $attributes['miles_in'] + $this->faker->numberBetween(50, 200),
            'image_out' => 'clockings/test_image_out.jpg',
        ]);
    }
}
