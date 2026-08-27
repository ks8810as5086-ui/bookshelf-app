<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_ranking_page(): void
    {
        $response = $this->get('/ranking');

        $response->assertOk();
    }

    public function test_books_are_ranked_by_average_rating_descending(): void
    {
        $user = User::factory()->create();

        $highRatedBook = Book::create([
            'user_id' => $user->id,
            'title' => '高評価書籍',
            'author' => '著者A',
        ]);

        $lowRatedBook = Book::create([
            'user_id' => $user->id,
            'title' => '低評価書籍',
            'author' => '著者B',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 5,
            'comment' => '高評価',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
            'comment' => '低評価',
        ]);

        $response = $this->get('/ranking');

        $response->assertOk();
        $response->assertSeeInOrder([
            '高評価書籍',
            '低評価書籍',
        ]);
    }

    public function test_book_without_reviews_is_not_displayed_in_ranking(): void
    {
        $user = User::factory()->create();

        $reviewedBook = Book::create([
            'user_id' => $user->id,
            'title' => 'レビューあり',
            'author' => '著者A',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => 'レビューなし',
            'author' => '著者B',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $reviewedBook->id,
            'rating' => 4,
            'comment' => 'レビュー',
        ]);

        $response = $this->get('/ranking');

        $response->assertOk();
        $response->assertSee('レビューあり');
        $response->assertDontSee('レビューなし');
    }

    public function test_ranking_displays_only_top_ten_books(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::create([
                'user_id' => $user->id,
                'title' => "ランキング書籍{$i}",
                'author' => "著者{$i}",
            ]);

            Review::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $i === 11 ? 1 : 5,
                'comment' => 'レビュー',
            ]);
        }

        $response = $this->get('/ranking');

        $response->assertOk();

        for ($i = 1; $i <= 10; $i++) {
            $response->assertSee("ランキング書籍{$i}");
        }

        $response->assertDontSee('ランキング書籍11');
    }
}
