<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Demo Customer',
            'email' => 'customer@technest.com',
            'phone_number' => '+60112233445',
            'password' => \Illuminate\Support\Facades\Hash::make('cust123'),
            'role' => 'user',
        ]);
    }
}
