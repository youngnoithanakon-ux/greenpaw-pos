<?php

namespace Tests\Feature;

use App\Models\SystemConfig;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    // TC-13-01: บันทึกชื่อร้าน
    public function test_admin_can_save_store_name(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post('/admin/settings', [
            'store_name'            => 'GreenPaw Test Shop',
            'stock_alert_threshold' => 5,
        ])->assertRedirect('/admin/settings');

        $this->assertDatabaseHas('system_configs', [
            'key'   => 'store_name',
            'value' => 'GreenPaw Test Shop',
        ]);
    }

    // TC-13-09: บันทึก stock threshold
    public function test_admin_can_save_stock_threshold(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post('/admin/settings', [
            'store_name'            => 'GreenPaw',
            'stock_alert_threshold' => 10,
        ])->assertRedirect('/admin/settings');

        $this->assertDatabaseHas('system_configs', [
            'key'   => 'stock_alert_threshold',
            'value' => '10',
        ]);
    }

    // settings index โหลดได้
    public function test_settings_index_loads(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->get('/admin/settings')->assertStatus(200);
    }

    // validation: store_name required
    public function test_store_name_required(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->post('/admin/settings', ['stock_alert_threshold' => 5])
             ->assertSessionHasErrors('store_name');
    }

    // SystemConfig::get/set
    public function test_system_config_get_set(): void
    {
        SystemConfig::set('test_key', 'hello', 'Test', 'general');
        $this->assertEquals('hello', SystemConfig::get('test_key'));
    }

    // SystemConfig::get default
    public function test_system_config_get_returns_default(): void
    {
        $this->assertEquals('fallback', SystemConfig::get('nonexistent_key', 'fallback'));
    }
}
