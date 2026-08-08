<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_is_redirected_to_admin_dashboard()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => 1,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin-test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_regular_user_is_redirected_to_dashboard()
    {
        $user = User::create([
            'name' => 'Normal User',
            'email' => 'user-test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => 'user-test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
    }
}
