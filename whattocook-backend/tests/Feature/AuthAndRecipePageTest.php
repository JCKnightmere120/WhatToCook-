<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndRecipePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_view_recipe_suggestions(): void
    {
        User::factory()->create([
            'name' => 'Sample User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Log in');
    }
}
