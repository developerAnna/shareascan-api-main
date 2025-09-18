<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the specific user for kharronreid@gmail.com
        User::factory()->create([
            'name' => 'Kharron Reid',
            'email' => 'kharronreid@gmail.com',
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed categories and products
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
