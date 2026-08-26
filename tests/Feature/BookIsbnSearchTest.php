<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookIsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_isbn_search_returns_book_data(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '吾輩は猫である',
                            'authors' => ['夏目漱石'],
                            'publishedDate' => '1905-01-01',
                            'description' => 'テスト用説明文',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/book.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/books/isbn/9784101010014');

        $response
            ->assertOk()
            ->assertJson([
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'published_date' => '1905-01-01',
                'description' => 'テスト用説明文',
                'image_url' => 'https://example.com/book.jpg',
            ]);
    }

    public function test_isbn_search_returns_error_when_book_is_not_found(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/books/isbn/9780000000000');

        $response
            ->assertNotFound()
            ->assertJson([
                'error' => '書籍が見つかりませんでした。',
            ]);
    }

    public function test_isbn_search_returns_error_when_api_request_fails(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 429),
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/books/isbn/9784101010014');

        $response
            ->assertStatus(502)
            ->assertJson([
                'error' => '書籍情報の取得に失敗しました。',
            ]);
    }
}
