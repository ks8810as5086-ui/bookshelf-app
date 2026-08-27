<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_book_to_favorites(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/books/{$book->id}/favorites");

        $response->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_authenticated_user_can_remove_book_from_favorites(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $user->favoriteBooks()->attach($book->id);

        $response = $this
            ->actingAs($user)
            ->post("/books/{$book->id}/favorites");

        $response->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_authenticated_user_can_view_favorites(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'お気に入り書籍',
            'author' => 'テスト著者',
        ]);

        $user->favoriteBooks()->attach($book->id);

        $response = $this
            ->actingAs($user)
            ->get('/favorites');

        $response->assertOk();
        $response->assertSee('お気に入り書籍');
    }

    public function test_guest_cannot_toggle_favorite(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this
            ->post("/books/{$book->id}/favorites");

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_view_favorites(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }
}
