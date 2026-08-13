<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    // TC-12-01
    public function test_admin_can_create_staff(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post('/admin/users', [
            'username' => 'newstaff',
            'fullname' => 'New Staff',
            'password' => 'password123',
            'role'     => 'staff',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', ['username' => 'newstaff', 'role' => 'staff']);
    }

    // TC-12-02
    public function test_duplicate_username_fails(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->makeStaff(['username' => 'dupuser']);

        $this->post('/admin/users', [
            'username' => 'dupuser',
            'fullname' => 'Dup',
            'password' => 'password',
            'role'     => 'staff',
        ])->assertSessionHasErrors('username');
    }

    // TC-12-04: แก้ไข fullname/role
    public function test_admin_can_update_user(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeStaff();
        $this->actingAs($admin);

        $this->put('/admin/users/' . $staff->id, [
            'fullname' => 'Updated Name',
            'role'     => 'admin',
        ])->assertRedirect('/admin/users');

        $this->assertEquals('Updated Name', $staff->fresh()->fullname);
        $this->assertEquals('admin', $staff->fresh()->role);
    }

    // TC-12-07
    public function test_admin_can_delete_user(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeStaff();
        $this->actingAs($admin);

        $this->delete('/admin/users/' . $staff->id)
             ->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    // TC-12-08: ลบตัวเองไม่ได้
    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->delete('/admin/users/' . $admin->id)
             ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    // TC-12-03: login ด้วย account ใหม่
    public function test_new_user_can_login(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $this->post('/admin/users', [
            'username' => 'freshuser',
            'fullname' => 'Fresh',
            'password' => 'mypassword',
            'role'     => 'staff',
        ]);

        $this->post('/logout');

        $this->post('/login', ['username' => 'freshuser', 'password' => 'mypassword'])
             ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }
}
