<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-27',
                'description' => 'テスト説明',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
        ]);

        $book = Book::where('isbn', '9781234567890')->firstOrFail();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $response->assertRedirect();
    }

    public function test_book_can_be_created_without_isbn_and_published_date(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '小説',
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/books', [
                'title' => 'ISBNなし書籍',
                'author' => 'テスト著者',
                'isbn' => null,
                'published_date' => null,
                'description' => null,
                'image_url' => null,
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('books', [
            'title' => 'ISBNなし書籍',
            'isbn' => null,
            'published_at' => null,
        ]);
    }

    public function test_book_creation_requires_title(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/books/create')
            ->post('/books', [
                'title' => '',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-27',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_book_creation_requires_author(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/books', [
                'title' => 'テスト書籍',
                'author' => '',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-27',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('author');
    }

    public function test_book_creation_requires_at_least_one_genre(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-27',
                'genres' => [],
            ]);

        $response->assertSessionHasErrors('genres');
    }

    public function test_book_creation_rejects_invalid_isbn_length(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '12345',
                'published_date' => '2026-08-27',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_book_creation_rejects_duplicate_isbn(): void
    {
        $owner = User::factory()->create();

        Book::create([
            'user_id' => $owner->id,
            'title' => '既存書籍',
            'author' => '既存著者',
            'isbn' => '9781234567890',
            'published_at' => '2026-08-27',
        ]);

        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($owner)
            ->post('/books', [
                'title' => '新規書籍',
                'author' => '新規著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-27',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_book_creation_rejects_invalid_image_url(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-27',
                'image_url' => 'invalid-url',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('image_url');
    }

    public function test_owner_can_update_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前',
            'author' => '著者',
            'isbn' => '9781234567890',
            'published_at' => '2026-08-27',
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/books/{$book->id}", [
                'title' => '更新後',
                'author' => '更新後著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-28',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後',
            'author' => '更新後著者',
            'isbn' => '9781234567890',
        ]);
    }

    public function test_update_allows_current_books_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前',
            'author' => '著者',
            'isbn' => '9781234567890',
            'published_at' => '2026-08-27',
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/books/{$book->id}", [
                'title' => '更新後',
                'author' => '著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-27',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionDoesntHaveErrors('isbn');
    }

    public function test_user_cannot_update_another_users_book(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '所有者の書籍',
            'author' => '著者',
            'isbn' => '9781234567890',
            'published_at' => '2026-08-27',
        ]);

        $response = $this
            ->actingAs($anotherUser)
            ->put("/books/{$book->id}", [
                'title' => '不正更新',
                'author' => '著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-27',
                'genres' => [$genre->id],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '所有者の書籍',
        ]);
    }

    public function test_owner_can_delete_book(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '削除対象',
            'author' => '著者',
            'isbn' => '9781234567890',
            'published_at' => '2026-08-27',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/books/{$book->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_book(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '所有者の書籍',
            'author' => '著者',
            'isbn' => '9781234567890',
            'published_at' => '2026-08-27',
        ]);

        $response = $this
            ->actingAs($anotherUser)
            ->delete("/books/{$book->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
