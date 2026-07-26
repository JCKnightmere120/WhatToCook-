<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Josh Allen Tipactipac',
                'email' => 'josh@whattocook.test',
                'password' => 'josh12345',
            ],
            [
                'name' => 'Jason Conopio',
                'email' => 'jason@whattocook.test',
                'password' => 'jason12345',
            ],
            [
                'name' => 'Jacob Matthews Villacorte',
                'email' => 'jacob@whattocook.test',
                'password' => 'jacob12345',
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'is_admin' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}