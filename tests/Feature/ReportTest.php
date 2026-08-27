<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_report(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response->assertOk();
    }

    public function test_report_displays_correct_summary(): void
    {
        $user = User::factory()->create();

        $books = Book::factory()->count(3)->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[2]->id,
            'rating' => 3,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['summary']['total_reviews'] === 3
                    && $stats['summary']['books_read'] === 3
                    && $stats['summary']['average_rating'] == 4.0;
            });
    }

    public function test_report_displays_correct_rating_distribution(): void
    {
        $user = User::factory()->create();

        $books = Book::factory()->count(3)->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[0]->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[1]->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $books[2]->id,
            'rating' => 3,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['rating_distribution']->values()->all()
                    === [0, 0, 1, 1, 1];
            });
    }
}
