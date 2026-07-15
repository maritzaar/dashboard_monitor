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
    public function test_the_application_returns_a_successful_response(): void
    {
        // Unauthenticated guests should be redirected to login
        $response = $this->get('/');
        $response->assertRedirect(route('login'));

        // Authenticated users should access the dashboard (home) successfully
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);

        // Authenticated users should access the monitoring pages successfully
        $response = $this->actingAs($user)->get(route('monitoring.working_hour'));
        $response->assertStatus(200);
    }
}
