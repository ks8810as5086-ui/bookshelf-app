<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_review(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/books/{$book->id}/reviews", [
                'rating' => 5,
                'comment' => 'とても良い本でした。',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても良い本でした。',
        ]);
    }

    public function test_review_rating_is_required(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/books/{$book->id}/reviews", [
                'rating' => '',
                'comment' => 'テストコメント',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_review_rating_must_be_between_one_and_five(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/books/{$book->id}/reviews", [
                'rating' => 6,
                'comment' => 'テストコメント',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_owner_can_update_review(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前',
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/reviews/{$review->id}", [
                'rating' => 5,
                'comment' => '更新後',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '更新後',
        ]);
    }

    public function test_user_cannot_update_another_users_review(): void
    {
        $reviewOwner = User::factory()->create();
        $anotherUser = User::factory()->create();

        $book = Book::create([
            'user_id' => $reviewOwner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '元のコメント',
        ]);

        $response = $this
            ->actingAs($anotherUser)
            ->put("/reviews/{$review->id}", [
                'rating' => 5,
                'comment' => '不正更新',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '元のコメント',
        ]);
    }

    public function test_owner_can_delete_review(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '削除対象',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/reviews/{$review->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_review(): void
    {
        $reviewOwner = User::factory()->create();
        $anotherUser = User::factory()->create();

        $book = Book::create([
            'user_id' => $reviewOwner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $review = Review::create([
            'user_id' => $reviewOwner->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '削除対象',
        ]);

        $response = $this
            ->actingAs($anotherUser)
            ->delete("/reviews/{$review->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
