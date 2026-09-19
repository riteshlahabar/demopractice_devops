<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_returns_a_successful_response(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_admin_dashboard_redirects_guests_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_returns_a_successful_response(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Administrator Login')
            ->assertSee('Bawaskar ERP');
    }
}
