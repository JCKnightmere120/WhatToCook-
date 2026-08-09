<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's default admin account.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@whattocook.test'],
            [
                'name' => 'WhatToCook Admin',
                'password' => Hash::make('Admin@123'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}