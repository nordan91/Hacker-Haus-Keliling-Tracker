<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed default admin and driver accounts.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'Admin Haus',
            'email'    => 'admin@haus.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '081234567890',
            'is_active' => true,
        ]);

        User::create([
            'name'     => 'Nanda Zora Venerdi',
            'email'    => 'nanda@haus.com',
            'password' => Hash::make('HausNanda2026!'),
            'role'     => 'driver',
            'phone'    => '081234567891',
            'is_active' => true,
        ]);
    }
}

