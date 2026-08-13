<?php

namespace Tests\Feature;

use App\Models\Sale;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    // TC-05-01
    public function test_dashboard_loads_for_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->get('/dashboard')->assertStatus(200);
    }

    // TC-05-05
    public function test_dashboard_loads_for_staff(): void
    {
        $this->actingAs($this->makeStaff());
        $this->get('/dashboard')->assertStatus(200);
    }

    // TC-05-02: void bill ไม่นับในยอด
    public function test_voided_sale_not_counted_in_summary(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        // สร้างบิลปกติและบิล void
        $this->makeSale($admin, ['total_amount' => 333, 'status' => 'normal']);
        $this->makeSale($admin, ['total_amount' => 444, 'status' => 'void']);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // ยอดรวมต้องเป็น 333 ไม่ใช่ 777
        $response->assertSee('333');
        $response->assertDontSee('777');
    }
}
