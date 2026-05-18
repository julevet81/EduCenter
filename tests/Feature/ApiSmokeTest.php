<?php

namespace Tests\Feature;

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_read_dashboard(): void
    {
        $this->seed();

        $token = $this->postJson('/api/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '12345678',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->getJson('/api/dashboard/summary')
            ->assertOk()
            ->assertJsonStructure(['counts', 'finance', 'recent']);
    }

    public function test_admin_can_create_student_in_own_branch(): void
    {
        $this->seed();

        $token = $this->postJson('/api/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '12345678',
        ])->assertOk()->json('token');

        $branch = Branch::firstOrFail();

        $this->withToken($token)
            ->postJson('/api/students', [
                'branch_id' => $branch->id,
                'first_name' => 'Ali',
                'last_name' => 'Benali',
                'gender' => 'male',
                'parent_phone' => '+213555000000',
            ])
            ->assertCreated()
            ->assertJsonPath('data.first_name', 'Ali');
    }
}
