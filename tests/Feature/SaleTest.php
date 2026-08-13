<?php

namespace Tests\Feature;

use App\Models\Sale;
use Tests\TestCase;

class SaleTest extends TestCase
{
    // TC-09-01: หน้า index โหลดได้
    public function test_sales_index_loads(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->get('/admin/sales')->assertStatus(200);
    }

    // TC-09-04: void บิลปกติ
    public function test_admin_can_void_a_normal_sale(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $sale    = $this->makeSale($admin);
        $this->makeSaleItem($sale, $product, ['qty' => 2]);
        $this->actingAs($admin);

        $this->post('/admin/sales/' . $sale->id . '/void')
             ->assertRedirect('/admin/sales');

        $this->assertEquals('void', $sale->fresh()->status);
    }

    // TC-09-04: สต็อกคืนหลัง void
    public function test_void_restores_stock(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 5]);
        $sale    = $this->makeSale($admin);
        $this->makeSaleItem($sale, $product, ['qty' => 3]);
        $this->actingAs($admin);

        $this->post('/admin/sales/' . $sale->id . '/void');

        $this->assertEquals(8, (float) $product->fresh()->stock_qty);
    }

    // TC-09-05: void ซ้ำไม่ได้
    public function test_cannot_void_already_voided_sale(): void
    {
        $admin = $this->makeAdmin();
        $sale  = $this->makeSale($admin, ['status' => 'void']);
        $this->actingAs($admin);

        $this->post('/admin/sales/' . $sale->id . '/void')
             ->assertSessionHas('error');

        $this->assertEquals('void', $sale->fresh()->status);
    }

    // TC-09-06: summary ไม่นับ void
    public function test_summary_excludes_void_sales(): void
    {
        $admin = $this->makeAdmin();
        $this->makeSale($admin, ['total_amount' => 300, 'status' => 'normal']);
        $this->makeSale($admin, ['total_amount' => 500, 'status' => 'void']);
        $this->actingAs($admin);

        $response = $this->get('/admin/sales');
        $response->assertStatus(200);
        // ยอดรวมต้อง 300 ไม่ใช่ 800
        $response->assertSee('300.00');
    }

    // TC-09-07: export CSV ดาวน์โหลดได้
    public function test_export_excel_returns_csv(): void
    {
        $admin = $this->makeAdmin();
        $this->makeSale($admin);
        $this->actingAs($admin);

        $response = $this->get('/admin/sales/export-excel?start_date=' . now()->toDateString() . '&end_date=' . now()->toDateString());
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // TC-09-03: AJAX items endpoint คืน JSON
    public function test_sale_items_endpoint_returns_json(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();
        $sale    = $this->makeSale($admin);
        $this->makeSaleItem($sale, $product);
        $this->actingAs($admin);

        $this->getJson('/admin/sales/' . $sale->id . '/items')
             ->assertStatus(200)
             ->assertJsonStructure([['id', 'qty', 'unit_price', 'product']]);
    }
}
