<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_book_index(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_guest_can_view_register_page(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_guest_is_redirected_to_login_from_book_create(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_favorites(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_genres(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_reading_plans(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_book_create(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/books/create');

        $response->assertOk();
    }
}
