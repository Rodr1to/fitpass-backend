<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;

class CompanyEmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    private User $hrAdmin;
    private User $employeeInSameCompany;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->hrAdmin = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'hr_admin',
        ]);
        $this->employeeInSameCompany = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'employee',
        ]);
    }

    public function test_hr_admin_can_get_their_company_employees(): void
    {
        $response = $this->actingAs($this->hrAdmin, 'sanctum')->getJson('/api/v1/company/users');

        $response->assertStatus(200);
        
        // --- THE FIX IS HERE ---
        // We expect only 1 employee, because the admin is filtered out of the list.
        $response->assertJsonCount(1, 'data'); 
        
        $response->assertJsonPath('success', true);
        $response->assertJsonFragment(['email' => $this->employeeInSameCompany->email]);
    }

    public function test_regular_employee_cannot_get_company_employees_list(): void
    {
        $response = $this->actingAs($this->employeeInSameCompany, 'sanctum')->getJson('/api/v1/company/users');
        $response->assertStatus(403);
    }

    public function test_hr_admin_can_create_a_new_employee_in_their_company(): void
    {
        $newEmployeeData = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->hrAdmin, 'sanctum')->postJson('/api/v1/company/users', $newEmployeeData);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Jane Doe');
        $this->assertDatabaseHas('users', [
            'email' => 'jane.doe@example.com',
            'company_id' => $this->company->id,
        ]);
    }
}