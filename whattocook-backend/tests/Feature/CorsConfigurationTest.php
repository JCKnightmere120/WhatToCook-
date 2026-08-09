<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsConfigurationTest extends TestCase
{
    public function test_cors_has_native_capacitor_origins_without_a_wildcard(): void
    {
        $origins = config('cors.allowed_origins');

        $this->assertContains('http://localhost', $origins);
        $this->assertContains('https://localhost', $origins);
        $this->assertContains('capacitor://localhost', $origins);
        $this->assertNotContains('*', $origins);
    }
}
