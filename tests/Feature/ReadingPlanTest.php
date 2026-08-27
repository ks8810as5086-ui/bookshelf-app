<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_reading_plans(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/reading-plans');

        $response->assertOk();
    }

    public function test_authenticated_user_can_create_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => '2026-09-30',
            ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-09-30',
            'status' => 'planned',
        ]);
    }

    public function test_user_cannot_edit_another_users_reading_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => '2026-09-30',
            'status' => 'planned',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->get("/reading-plans/{$readingPlan->id}/edit");

        $response->assertForbidden();
    }

    public function test_user_can_complete_own_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-09-30',
            'status' => 'planned',
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/reading-plans/{$readingPlan->id}/complete");

        $response->assertRedirect(route('reading-plans.index'));

        $readingPlan->refresh();

        $this->assertSame('completed', $readingPlan->status->value);
        $this->assertNotNull($readingPlan->completed_at);
    }
}
