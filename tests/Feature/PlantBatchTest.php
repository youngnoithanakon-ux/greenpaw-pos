<?php

namespace Tests\Feature;

use App\Models\PlantBatch;
use Tests\TestCase;

class PlantBatchTest extends TestCase
{
    // TC-11-01
    public function test_admin_can_create_plant_batch(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['product_type' => 'produced']);
        $this->actingAs($admin);

        $this->post('/admin/plant-batches', [
            'product_id'            => $product->id,
            'plant_date'            => now()->toDateString(),
            'expected_harvest_date' => now()->addDays(14)->toDateString(),
            'qty_planted'           => 20,
        ])->assertRedirect('/admin/plant-batches');

        $this->assertDatabaseHas('plant_batches', [
            'product_id'  => $product->id,
            'qty_planted' => 20,
        ]);
    }

    // TC-11-04: auto growing→ready เมื่อถึงวัน
    public function test_batch_auto_becomes_ready_when_harvest_date_reached(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();
        $this->actingAs($admin);

        PlantBatch::create([
            'product_id'            => $product->id,
            'created_by'            => $admin->id,
            'plant_date'            => now()->subDays(14)->toDateString(),
            'expected_harvest_date' => now()->subDay()->toDateString(),
            'qty_planted'           => 10,
            'status'                => 'growing',
        ]);

        // โหลดหน้า index จะ trigger auto update
        $this->get('/admin/plant-batches')->assertStatus(200);

        $this->assertEquals('ready', PlantBatch::first()->fresh()->status);
    }

    // TC-11-07: harvest เพิ่มสต็อก
    public function test_harvest_increases_product_stock(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 5]);
        $batch   = PlantBatch::create([
            'product_id'            => $product->id,
            'created_by'            => $admin->id,
            'plant_date'            => now()->subDays(14)->toDateString(),
            'expected_harvest_date' => now()->subDay()->toDateString(),
            'qty_planted'           => 10,
            'status'                => 'ready',
        ]);
        $this->actingAs($admin);

        $this->post('/admin/plant-batches/' . $batch->id . '/harvest', [
            'qty_harvested'       => 8,
            'actual_harvest_date' => now()->toDateString(),
        ])->assertRedirect('/admin/plant-batches');

        $this->assertEquals(13, (float) $product->fresh()->stock_qty);
    }

    // TC-11-08: StockLog บันทึกหลัง harvest
    public function test_harvest_creates_stock_log(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 0]);
        $batch   = PlantBatch::create([
            'product_id'            => $product->id,
            'created_by'            => $admin->id,
            'plant_date'            => now()->subDays(14)->toDateString(),
            'expected_harvest_date' => now()->subDay()->toDateString(),
            'qty_planted'           => 10,
            'status'                => 'ready',
        ]);
        $this->actingAs($admin);

        $this->post('/admin/plant-batches/' . $batch->id . '/harvest', [
            'qty_harvested'       => 10,
            'actual_harvest_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'type'       => 'in',
            'qty_change' => 10,
        ]);
    }

    // TC-11-07: status เปลี่ยนเป็น harvested
    public function test_harvest_changes_status_to_harvested(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();
        $batch   = PlantBatch::create([
            'product_id'            => $product->id,
            'created_by'            => $admin->id,
            'plant_date'            => now()->subDays(14)->toDateString(),
            'expected_harvest_date' => now()->subDay()->toDateString(),
            'qty_planted'           => 10,
            'status'                => 'ready',
        ]);
        $this->actingAs($admin);

        $this->post('/admin/plant-batches/' . $batch->id . '/harvest', [
            'qty_harvested'       => 10,
            'actual_harvest_date' => now()->toDateString(),
        ]);

        $this->assertEquals('harvested', $batch->fresh()->status);
    }

    // TC-11-09: fail เปลี่ยน status
    public function test_fail_changes_status_to_failed(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();
        $batch   = PlantBatch::create([
            'product_id'            => $product->id,
            'created_by'            => $admin->id,
            'plant_date'            => now()->toDateString(),
            'expected_harvest_date' => now()->addDays(7)->toDateString(),
            'qty_planted'           => 5,
            'status'                => 'growing',
        ]);
        $this->actingAs($admin);

        $this->post('/admin/plant-batches/' . $batch->id . '/fail', ['fail_notes' => 'เชื้อรา'])
             ->assertRedirect('/admin/plant-batches');

        $this->assertEquals('failed', $batch->fresh()->status);
    }

    // TC-11-09: fail ไม่เปลี่ยนสต็อก
    public function test_fail_does_not_change_stock(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(['stock_qty' => 10]);
        $batch   = PlantBatch::create([
            'product_id'            => $product->id,
            'created_by'            => $admin->id,
            'plant_date'            => now()->toDateString(),
            'expected_harvest_date' => now()->addDays(7)->toDateString(),
            'qty_planted'           => 5,
            'status'                => 'growing',
        ]);
        $this->actingAs($admin);

        $this->post('/admin/plant-batches/' . $batch->id . '/fail', ['fail_notes' => 'test']);
        $this->assertEquals(10, (float) $product->fresh()->stock_qty);
    }
}
