<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Demo Guest',
            'email' => 'guest@example.com',
            'password' => Hash::make('password'),
            'role' => 'guest',
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@emes.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    }
}
