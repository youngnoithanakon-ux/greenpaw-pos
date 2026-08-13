<?php

namespace Tests\Feature;

use App\Models\Category;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    // TC-07-01
    public function test_admin_can_add_category(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post('/admin/categories', ['category_name' => 'หญ้าแมวพันธุ์ดี'])
             ->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', ['category_name' => 'หญ้าแมวพันธุ์ดี']);
    }

    // TC-07-02: ชื่อซ้ำ
    public function test_duplicate_category_name_fails(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->makeCategory('ซ้ำ');

        $this->post('/admin/categories', ['category_name' => 'ซ้ำ'])
             ->assertSessionHasErrors('category_name');
    }

    // TC-07-03
    public function test_admin_can_delete_category(): void
    {
        $admin = $this->makeAdmin();
        $cat   = $this->makeCategory('ลบได้');
        $this->actingAs($admin);

        $this->delete('/admin/categories/' . $cat->id)
             ->assertRedirect('/admin/categories');

        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }

    // TC-07-04: ปรากฏในหน้า index
    public function test_category_appears_in_index(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->makeCategory('หมวดทดสอบ');

        $this->get('/admin/categories')->assertSee('หมวดทดสอบ');
    }
}
