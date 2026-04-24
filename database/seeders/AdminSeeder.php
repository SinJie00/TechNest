<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@technest.com'],
            [
                'name' => 'System Admin',
                'phone_number' => '+60123456789',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        echo "Admin account created: admin@technest.com / admin123\n";
    }
}
