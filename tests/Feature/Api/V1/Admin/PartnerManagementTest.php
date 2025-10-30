<?php

namespace Tests\Feature\Api\V1\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Partner;

class PartnerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $hrAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->hrAdmin = User::factory()->create(['role' => 'hr_admin']);
    }

    public function test_super_admin_can_create_a_partner(): void
    {
        $partnerData = [
            'name' => 'New Global Gym',
            'type' => 'gym',
            'city' => 'Lima',
            'address' => '456 Av. Arequipa',
            'latitude' => -12.0888,
            'longitude' => -77.0503,
            'contact_email' => 'contact@globalgym.com',
            'status' => 'approved', // <-- THIS IS THE FIX
        ];

        $response = $this->actingAs($this->superAdmin, 'sanctum')
                         ->postJson('/api/v1/admin/partners', $partnerData);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.name', 'New Global Gym');
        
        $this->assertDatabaseHas('partners', ['name' => 'New Global Gym']);
    }

    public function test_hr_admin_cannot_create_a_partner(): void
    {
        // This test sends incomplete data on purpose, which is fine
        // because we only care that the status code is 403 (Forbidden).
        $response = $this->actingAs($this->hrAdmin, 'sanctum')
                         ->postJson('/api/v1/admin/partners', ['name' => 'Test']);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_create_a_partner(): void
    {
        $response = $this->postJson('/api/v1/admin/partners', ['name' => 'Test']);

        $response->assertStatus(401);
    }
}