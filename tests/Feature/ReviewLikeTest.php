<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_like_review(): void
    {
        $reviewOwner = User::factory()->create();
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $reviewOwner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '良い本でした。',
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/reviews/{$review->id}/like");

        $response->assertRedirect();

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_authenticated_user_can_remove_like_from_review(): void
    {
        $reviewOwner = User::factory()->create();
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $reviewOwner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'テストレビュー',
        ]);

        $review->likedByUsers()->attach($user->id);

        $response = $this
            ->actingAs($user)
            ->post("/reviews/{$review->id}/like");

        $response->assertRedirect();

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_multiple_users_can_like_same_review(): void
    {
        $reviewOwner = User::factory()->create();
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $book = Book::create([
            'user_id' => $reviewOwner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビュー',
        ]);

        $this
            ->actingAs($firstUser)
            ->post("/reviews/{$review->id}/like");

        $this
            ->actingAs($secondUser)
            ->post("/reviews/{$review->id}/like");

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $firstUser->id,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $secondUser->id,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseCount('review_likes', 2);
    }

    public function test_guest_cannot_like_review(): void
    {
        $reviewOwner = User::factory()->create();

        $book = Book::create([
            'user_id' => $reviewOwner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビュー',
        ]);

        $response = $this
            ->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }
}
