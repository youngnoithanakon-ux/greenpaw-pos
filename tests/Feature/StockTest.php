<?php

namespace Tests\Feature;

use Tests\TestCase;

class StockTest extends TestCase
{
    // TC-08-01
    public function test_admin_can_add_stock(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 5]);
        $this->actingAs($admin);

        $this->post('/admin/stock', [
            'product_id' => $product->id,
            'add_qty'    => 10,
        ])->assertRedirect('/admin/stock');

        $this->assertEquals(15, $product->fresh()->stock_qty);
    }

    // TC-08-02: qty จำนวนเต็ม
    public function test_add_integer_stock(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 5]);
        $this->actingAs($admin);

        $this->post('/admin/stock', ['product_id' => $product->id, 'add_qty' => 3]);
        $this->assertEquals(8, $product->fresh()->stock_qty);
    }

    // TC-08-03: inactive ไม่ได้
    public function test_cannot_add_stock_to_inactive_product(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['status' => 0, 'stock_qty' => 5]);
        $this->actingAs($admin);

        $this->post('/admin/stock', ['product_id' => $product->id, 'add_qty' => 10])
             ->assertSessionHas('error');

        $this->assertEquals(5, $product->fresh()->stock_qty);
    }

    // TC-08-04: StockLog บันทึก
    public function test_stock_log_created_after_add(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 0]);
        $this->actingAs($admin);

        $this->post('/admin/stock', ['product_id' => $product->id, 'add_qty' => 10]);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'type'       => 'in',
            'qty_change' => 10,
            'qty_after'  => 10,
        ]);
    }

    // TC-08-05: stock log page loads
    public function test_stock_log_page_loads(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->get('/admin/stock/log')->assertStatus(200);
    }
}
