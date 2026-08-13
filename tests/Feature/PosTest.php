<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockLog;
use Tests\TestCase;

class PosTest extends TestCase
{
    // TC-02-01
    public function test_add_product_to_cart(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 1])
             ->assertRedirect('/pos');

        $cart = session('cart');
        $this->assertArrayHasKey($product->id, $cart);
        $this->assertEquals(1, $cart[$product->id]['qty']);
    }

    // TC-02-02
    public function test_adding_same_product_increases_qty(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 2]);
        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 3]);

        $cart = session('cart');
        $this->assertEquals(5, $cart[$product->id]['qty']);
    }

    // TC-02-03
    public function test_cannot_add_more_than_stock(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 3]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 5])
             ->assertRedirect('/pos')
             ->assertSessionHas('error');

        $this->assertEmpty(session('cart'));
    }

    // TC-02-05
    public function test_remove_item_from_cart(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 1]);
        $this->get('/pos/remove/' . $product->id)->assertRedirect('/pos');

        $this->assertEmpty(session('cart'));
    }

    // TC-02-07
    public function test_add_decimal_qty(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 0.5])
             ->assertRedirect('/pos');

        $cart = session('cart');
        $this->assertEquals(0.5, $cart[$product->id]['qty']);
    }

    // TC-04-01: checkout สำเร็จ redirect ไป receipt
    public function test_checkout_creates_sale_and_redirects_to_receipt(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 2]);
        $response = $this->post('/pos/checkout', ['received_amount' => 300]);

        $sale = Sale::latest()->first();
        $response->assertRedirect('/pos/receipt/' . $sale->id);
    }

    // TC-04-03: สต็อกลดหลัง checkout
    public function test_checkout_decrements_stock(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 3]);
        $this->post('/pos/checkout', ['received_amount' => 300]);

        $this->assertEquals(7, $product->fresh()->stock_qty);
    }

    // TC-04-04: StockLog บันทึก type=out
    public function test_checkout_creates_stock_log(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 2]);
        $this->post('/pos/checkout', ['received_amount' => 200]);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'type'       => 'out',
            'qty_change' => 2,
        ]);
    }

    // TC-04-05: ตะกร้าว่างหลัง checkout
    public function test_cart_cleared_after_checkout(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 1]);
        $this->post('/pos/checkout', ['received_amount' => 100]);

        $this->assertEmpty(session('cart'));
    }

    // TC-04-06: checkout ตะกร้าว่าง
    public function test_checkout_with_empty_cart_returns_error(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->post('/pos/checkout', ['received_amount' => 0])
             ->assertRedirect('/pos')
             ->assertSessionHas('error');
    }

    // TC-04-02: receipt แสดงข้อมูลครบ
    public function test_receipt_page_shows_sale_data(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 1]);
        $this->post('/pos/checkout', ['received_amount' => 150]);

        $sale = Sale::latest()->first();
        $this->get('/pos/receipt/' . $sale->id)
             ->assertStatus(200)
             ->assertSee('GreenPaw')
             ->assertSee(str_pad($sale->id, 5, '0', STR_PAD_LEFT));
    }

    // TC-04: received_amount บันทึกใน DB
    public function test_received_amount_saved_in_sale(): void
    {
        $user    = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $this->actingAs($user);

        $this->post('/pos/add', ['product_id' => $product->id, 'qty' => 1]);
        $this->post('/pos/checkout', ['received_amount' => 500]);

        $sale = Sale::latest()->first();
        $this->assertEquals(500.00, (float) $sale->received_amount);
    }
}
