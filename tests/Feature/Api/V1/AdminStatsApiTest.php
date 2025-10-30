<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Checkin;

class AdminStatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_get_platform_overview()
    {
    // Arrange
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    Company::factory()->count(3)->create();
    Partner::factory()->count(5)->create(['status' => 'approved']);
    Partner::factory()->count(2)->create(['status' => 'pending']); // Should not be counted

    // Act
    $response = $this->actingAs($superAdmin, 'sanctum')->getJson('/api/v1/admin/stats/platform-overview');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.total_companies', 3);
    $response->assertJsonPath('data.total_approved_partners', 5);
    
    // --- THIS IS THE FIX ---
    // First, get the 'data' part of the JSON response
    $responseData = $response->json('data');
    // Then, use a standard PHPUnit assertion to check the type
    $this->assertIsInt($responseData['total_users']);
    }
    
    public function test_hr_admin_cannot_get_platform_overview()
    {
        // Arrange
        $hrAdmin = User::factory()->create(['role' => 'hr_admin']);

        // Act
        $response = $this->actingAs($hrAdmin, 'sanctum')->getJson('/api/v1/admin/stats/platform-overview');

        // Assert
        $response->assertStatus(403); // Forbidden
    }
}