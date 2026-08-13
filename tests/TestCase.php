<?php

namespace Tests;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    // ---- User helpers ----

    protected function makeAdmin(array $overrides = []): User
    {
        return User::create(array_merge([
            'username' => 'admin',
            'fullname' => 'Admin User',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ], $overrides));
    }

    protected function makeStaff(array $overrides = []): User
    {
        return User::create(array_merge([
            'username' => 'staff01',
            'fullname' => 'Staff One',
            'password' => bcrypt('password'),
            'role'     => 'staff',
        ], $overrides));
    }

    // ---- Data helpers ----

    protected function makeCategory(string $name = 'หญ้าแมว'): Category
    {
        return Category::create(['category_name' => $name]);
    }

    protected function makeProduct(array $overrides = []): Product
    {
        $cat = $this->makeCategory();
        return Product::create(array_merge([
            'category_id'     => $cat->id,
            'product_name'    => 'หญ้าแมว Test',
            'product_type'    => 'produced',
            'cost_price'      => 30.00,
            'sale_price'      => 100.00,
            'consignment_fee' => 10.00,
            'stock_qty'       => 20,
            'status'          => 1,
        ], $overrides));
    }

    protected function makeSale(User $user, array $overrides = []): Sale
    {
        return Sale::create(array_merge([
            'user_id'               => $user->id,
            'total_amount'          => 200.00,
            'total_cost'            => 60.00,
            'total_consignment_fee' => 20.00,
            'received_amount'       => 200.00,
            'status'                => 'normal',
        ], $overrides));
    }

    protected function makeSaleItem(Sale $sale, Product $product, array $overrides = []): SaleItem
    {
        return SaleItem::create(array_merge([
            'sale_id'         => $sale->id,
            'product_id'      => $product->id,
            'qty'             => 2,
            'unit_price'      => 100.00,
            'unit_cost'       => 30.00,
            'consignment_fee' => 10.00,
        ], $overrides));
    }
}
