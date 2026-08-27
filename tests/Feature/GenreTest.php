<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_genre_index(): void
    {
        $user = User::factory()->create();

        Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/genres');

        $response->assertOk();
        $response->assertSee('技術書');
    }

    public function test_authenticated_user_can_create_genre(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/genres', [
                'name' => '新規ジャンル',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('genres', [
            'name' => '新規ジャンル',
        ]);
    }

    public function test_genre_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/genres', [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_genre_name_must_be_unique(): void
    {
        $user = User::factory()->create();

        Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/genres', [
                'name' => '技術書',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_authenticated_user_can_update_genre(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '更新前',
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/genres/{$genre->id}", [
                'name' => '更新後',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新後',
        ]);
    }

    public function test_update_allows_current_genre_name(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/genres/{$genre->id}", [
                'name' => '技術書',
            ]);

        $response->assertSessionDoesntHaveErrors('name');
    }

    public function test_authenticated_user_can_delete_genre_without_books(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '削除対象',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/genres/{$genre->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this
            ->actingAs($user)
            ->delete("/genres/{$genre->id}");

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);

        $response->assertRedirect();
    }
}
