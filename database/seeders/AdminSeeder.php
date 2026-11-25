<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@vaksinasi.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin12345!'),
                'role' => 'admin',
                'status_akun' => 'diterima',
                'email_verified_at' => now(),
            ]
        );
    }
}
