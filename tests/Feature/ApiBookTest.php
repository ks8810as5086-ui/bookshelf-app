<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiBookTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_get_book_list(): void
    {
        $user = User::factory()->create();

        Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_guest_can_get_book_detail(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '詳細テスト書籍',
            'author' => '詳細テスト著者',
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $book->id,
            'title' => '詳細テスト書籍',
            'author' => '詳細テスト著者',
        ]);
    }

    public function test_book_detail_returns_not_found_for_nonexistent_book(): void
    {
        $response = $this->getJson('/api/v1/books/999999');

        $response->assertNotFound();
    }

    public function test_guest_cannot_create_book(): void
    {
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $response = $this->postJson('/api/v1/books', [
            'title' => '未認証書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-08-27',
            'genres' => [$genre->id],
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'API登録書籍',
            'author' => 'API著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-08-27',
            'description' => 'API登録テスト',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'API登録書籍',
            'author' => 'API著者',
            'isbn' => '9781234567890',
        ]);

        $book = Book::where('isbn', '9781234567890')->firstOrFail();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_api_book_creation_returns_validation_error(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => '',
            'author' => '',
            'isbn' => '123',
            'genres' => [],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'title',
            'author',
            'isbn',
            'genres',
        ]);
    }

    public function test_owner_can_update_book_via_api(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前',
            'author' => '更新前著者',
            'isbn' => '9781234567890',
            'published_at' => '2026-08-27',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後',
            'author' => '更新後著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-08-28',
            'genres' => [$genre->id],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後',
            'author' => '更新後著者',
        ]);
    }

    public function test_user_cannot_update_another_users_book_via_api(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '所有者の書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
        ]);

        Sanctum::actingAs($anotherUser);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '不正更新',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '所有者の書籍',
        ]);
    }

    public function test_guest_cannot_update_book(): void
    {
        $owner = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this->putJson("/api/v1/books/{$book->id}", []);

        $response->assertUnauthorized();
    }

    public function test_owner_can_delete_book_via_api(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '削除対象',
            'author' => 'テスト著者',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_book_via_api(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '所有者の書籍',
            'author' => 'テスト著者',
        ]);

        Sanctum::actingAs($anotherUser);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_guest_cannot_delete_book(): void
    {
        $owner = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertUnauthorized();
    }

    public function test_book_list_contains_resource_fields(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Resourceテスト書籍',
            'author' => 'Resource著者',
            'isbn' => '9781234567890',
            'published_at' => '2026-08-27',
            'description' => 'Resourceテスト',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $book->genres()->attach($genre->id);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '良い本でした。',
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'genres',
                    'average_rating',
                    'reviews_count',
                ],
            ],
            'links',
            'meta',
        ]);

        $response->assertJsonFragment([
            'title' => 'Resourceテスト書籍',
            'name' => '技術書',
            'average_rating' => 5.0,
            'reviews_count' => 1,
        ]);
    }

    public function test_book_detail_contains_genres_and_reviews(): void
    {
        $user = User::factory()->create([
            'name' => 'レビュー投稿者',
        ]);

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '詳細Resource書籍',
            'author' => '詳細著者',
        ]);

        $book->genres()->attach($genre->id);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '詳細レビュー',
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'reviews' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name',
                        ],
                        'rating',
                        'comment',
                        'created_at',
                    ],
                ],
            ],
        ]);

        $response->assertJsonFragment([
            'name' => 'レビュー投稿者',
            'rating' => 4,
            'comment' => '詳細レビュー',
        ]);
    }
}
