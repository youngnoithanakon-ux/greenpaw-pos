<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    // TC-01-01
    public function test_admin_can_login_and_redirect_to_home(): void
    {
        $this->makeAdmin();
        $this->post('/login', ['username' => 'admin', 'password' => 'password'])
             ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    // TC-01-02
    public function test_staff_can_login(): void
    {
        $this->makeStaff();
        $this->post('/login', ['username' => 'staff01', 'password' => 'password'])
             ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    // TC-01-03
    public function test_login_fails_with_wrong_password(): void
    {
        $this->makeAdmin();
        $this->post('/login', ['username' => 'admin', 'password' => 'wrong'])
             ->assertSessionHasErrors();
        $this->assertGuest();
    }

    // TC-01-04
    public function test_login_fails_with_unknown_username(): void
    {
        $this->post('/login', ['username' => 'ghost', 'password' => 'password'])
             ->assertSessionHasErrors();
        $this->assertGuest();
    }

    // TC-01-05
    public function test_guest_redirected_to_login(): void
    {
        $this->get('/pos')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/products')->assertRedirect('/login');
    }

    // TC-01-06
    public function test_staff_gets_403_on_admin_routes(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff);
        $this->get('/admin/products')->assertStatus(403);
        $this->get('/admin/users')->assertStatus(403);
        $this->get('/admin/reports')->assertStatus(403);
        $this->get('/admin/settings')->assertStatus(403);
        $this->get('/admin/sales')->assertStatus(403);
    }

    // TC-01-07
    public function test_logout_clears_session(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
