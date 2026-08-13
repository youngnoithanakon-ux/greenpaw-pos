<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfileTest extends TestCase
{
    // TC-14-01: แก้ไข username
    public function test_user_can_update_username(): void
    {
        $user = $this->makeAdmin();
        $this->actingAs($user);

        $response = $this->patch('/profile', [
            'username' => 'newadmin',
            'fullname' => $user->fullname,
        ]);

        $response->assertSessionHasNoErrors()
                 ->assertRedirect('/profile');

        $this->assertEquals('newadmin', $user->fresh()->username);
    }

    // TC-14-02: แก้ไข fullname
    public function test_user_can_update_fullname(): void
    {
        $user = $this->makeAdmin();
        $this->actingAs($user);

        $this->patch('/profile', [
            'username' => $user->username,
            'fullname' => 'New Full Name',
        ])->assertRedirect('/profile');

        $this->assertEquals('New Full Name', $user->fresh()->fullname);
    }

    // TC-14-03: username ซ้ำกับคนอื่น
    public function test_cannot_use_duplicate_username(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeStaff();
        $this->actingAs($admin);

        $this->patch('/profile', [
            'username' => $staff->username,
            'fullname' => 'Admin',
        ])->assertSessionHasErrors('username');
    }

    // TC-14-04: เปลี่ยนรหัสผ่าน
    public function test_user_can_change_password(): void
    {
        $user = $this->makeAdmin();
        $this->actingAs($user);

        $this->put('/password', [
            'current_password'      => 'password',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect();

        // login ด้วยรหัสใหม่ได้
        $this->post('/logout');
        $this->post('/login', ['username' => 'admin', 'password' => 'newpassword123'])
             ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    // TC-14-05: current password ผิด
    public function test_wrong_current_password_fails(): void
    {
        $user = $this->makeAdmin();
        $this->actingAs($user);

        $response = $this->put('/password', [
            'current_password'      => 'wrongpassword',
            'password'              => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $response->assertSessionHasErrors(['current_password'], null, 'updatePassword');
    }
}
