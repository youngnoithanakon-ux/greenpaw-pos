<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    // TC-06-01
    public function test_admin_can_create_product(): void
    {
        $admin = $this->makeAdmin();
        $cat   = $this->makeCategory();
        $this->actingAs($admin);

        $this->post('/admin/products', [
            'product_name'    => 'หญ้าแมวทดสอบ',
            'product_type'    => 'produced',
            'sale_price'      => 120,
            'consignment_fee' => 10,
            'status'          => 1,
            'category_id'     => $cat->id,
            'seed_cost'       => 10,
            'soil_cost'       => 5,
            'pot_cost'        => 5,
            'labor_cost'      => 5,
            'waste_percent'   => 10,
        ])->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', ['product_name' => 'หญ้าแมวทดสอบ']);
        $this->assertEquals(0, Product::where('product_name', 'หญ้าแมวทดสอบ')->first()->stock_qty);
    }

    // TC-06-02: cost_price คำนวณถูกต้อง
    public function test_cost_price_calculated_correctly_for_produced(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->post('/admin/products', [
            'product_name'  => 'Test Cost',
            'product_type'  => 'produced',
            'sale_price'    => 100,
            'status'        => 1,
            'seed_cost'     => 10,
            'soil_cost'     => 5,
            'pot_cost'      => 5,
            'labor_cost'    => 5,
            'waste_percent' => 0,
        ]);

        $product = Product::where('product_name', 'Test Cost')->first();
        // (10+5+5+5) × (1 + 0/100) = 25.00
        $this->assertEquals(25.00, (float) $product->cost_price);
    }

    // TC-06-03: bought product ใช้ fixed_cost
    public function test_cost_price_for_bought_product(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post('/admin/products', [
            'product_name' => 'Bought Product',
            'product_type' => 'bought',
            'sale_price'   => 100,
            'status'       => 1,
            'fixed_cost'   => 60,
        ]);

        $product = Product::where('product_name', 'Bought Product')->first();
        $this->assertEquals(60.00, (float) $product->cost_price);
    }

    // TC-06-06: แก้ไขสินค้า
    public function test_admin_can_update_product(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();
        $this->actingAs($admin);

        $this->put('/admin/products/' . $product->id, [
            'product_name' => 'Updated Name',
            'product_type' => 'produced',
            'sale_price'   => 200,
            'status'       => 1,
        ])->assertRedirect('/admin/products');

        $this->assertEquals('Updated Name', $product->fresh()->product_name);
        $this->assertEquals(200, (float) $product->fresh()->sale_price);
    }

    // TC-06-08: toggleStatus
    public function test_toggle_status_changes_product_status(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['status' => 1]);
        $this->actingAs($admin);

        $this->patch('/admin/products/' . $product->id . '/toggle');
        $this->assertEquals(0, (int) $product->fresh()->status);

        $this->patch('/admin/products/' . $product->id . '/toggle');
        $this->assertEquals(1, (int) $product->fresh()->status);
    }

    // TC-06-09: destroy ทำ inactive ไม่ลบจริง
    public function test_destroy_makes_product_inactive(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['status' => 1]);
        $this->actingAs($admin);

        $this->delete('/admin/products/' . $product->id);
        $this->assertEquals(0, (int) $product->fresh()->status);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // TC-06-01: sale_price required
    public function test_product_requires_sale_price(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post('/admin/products', [
            'product_name' => 'No Price',
            'product_type' => 'produced',
            'status'       => 1,
        ])->assertSessionHasErrors('sale_price');
    }

    // TC-06-11: index ต้องแสดงรายการ
    public function test_product_index_shows_products(): void
    {
        $admin = $this->makeAdmin();
        $this->makeProduct(['product_name' => 'หญ้าแมว A']);
        $this->actingAs($admin);

        $this->get('/admin/products')->assertStatus(200)->assertSee('หญ้าแมว A');
    }
}
