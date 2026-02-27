<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'Admin FlashTicket',
            'email' => 'admin@example.com',
            'password' => Hash::make('Qwerty123'),
            'role' => 'admin',
        ]);

        // Customer User
        User::factory()->create([
            'name' => 'Customer FlashTicket',
            'email' => 'user@example.com',
            'password' => Hash::make('Qwerty123'),
            'role' => 'customer',
        ]);
    }
}
