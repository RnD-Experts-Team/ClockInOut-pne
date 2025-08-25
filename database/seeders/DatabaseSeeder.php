<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        //     'role' => 'user',
        //     'password' => Hash::make('password123'),
        // ]);

        User::factory()->create([
            'name' => 'Jaden',
            'email' => 'jaden@pnefoods.com',
            'role' => 'admin',
            'password' => Hash::make('Jaden123456'),
        ]);
    }
}
