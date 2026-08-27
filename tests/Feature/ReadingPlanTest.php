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

    public function test_user_can_update_own_reading_plan_target_date(): void
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
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => '2026-10-15',
            ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2026-10-15',
        ]);
    }

    public function test_user_cannot_update_another_users_reading_plan(): void
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
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => '2026-10-15',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2026-09-30',
        ]);
    }

    public function test_user_can_delete_own_reading_plan(): void
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
            ->delete("/reading-plans/{$readingPlan->id}");

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_reading_plan(): void
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
            ->delete("/reading-plans/{$readingPlan->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_reading_plans_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();

        $plannedBook = Book::factory()->create([
            'title' => '予定中の本',
        ]);

        $completedBook = Book::factory()->create([
            'title' => '読了済みの本',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $plannedBook->id,
            'target_date' => '2026-09-30',
            'status' => 'planned',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $completedBook->id,
            'target_date' => '2026-09-30',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reading-plans?status=planned');

        $response->assertOk();
        $response->assertSee('予定中の本');
        $response->assertDontSee('読了済みの本');
    }
}
