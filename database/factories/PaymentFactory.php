<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store_id' => Store::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'description' => $this->faker->sentence(),
            'receipt_path' => 'receipts/test_receipt.jpg',
            'is_admin_equipment' => false,
            'maintenance_request_id' => null,
        ];
    }

    public function adminEquipment(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin_equipment' => true,
        ]);
    }
}
