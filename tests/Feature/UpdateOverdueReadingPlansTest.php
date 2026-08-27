<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOverdueReadingPlansTest extends TestCase
{
    use RefreshDatabase;

    public function test_past_planned_reading_plan_is_updated_to_overdue(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $this->artisan('app:update-overdue-reading-plans')
            ->assertSuccessful();

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::Overdue,
            $readingPlan->status
        );
    }

    public function test_today_planned_reading_plan_is_not_updated_to_overdue(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $this->artisan('app:update-overdue-reading-plans')
            ->assertSuccessful();

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::Planned,
            $readingPlan->status
        );
    }

    public function test_future_planned_reading_plan_is_not_updated_to_overdue(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDay()->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $this->artisan('app:update-overdue-reading-plans')
            ->assertSuccessful();

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::Planned,
            $readingPlan->status
        );
    }

    public function test_past_completed_reading_plan_is_not_updated_to_overdue(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now()->subHours(2),
        ]);

        $this->artisan('app:update-overdue-reading-plans')
            ->assertSuccessful();

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::Completed,
            $readingPlan->status
        );
    }

    public function test_existing_overdue_reading_plan_remains_overdue(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDays(5)->toDateString(),
            'status' => ReadingPlanStatus::Overdue,
        ]);

        $this->artisan('app:update-overdue-reading-plans')
            ->assertSuccessful();

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::Overdue,
            $readingPlan->status
        );
    }
}
