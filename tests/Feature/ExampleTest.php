<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_dashboard_requires_authentication(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('http://auth.example.com/login');
    }

    public function test_the_dashboard_returns_a_successful_response_for_authenticated_users(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk();
    }
}
