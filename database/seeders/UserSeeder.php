<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@man2surakarta.id',
            'password' => Hash::make('password123'), // Ubah password ini
        ]);

        // Create additional test users
        User::create([
            'name' => 'Guru Admin',
            'username' => 'guru_admin',
            'email' => 'guru@man2surakarta.id',
            'password' => Hash::make('password123'),
        ]);
    }
}
