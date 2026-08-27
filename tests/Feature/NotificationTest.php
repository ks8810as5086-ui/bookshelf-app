<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_notifications(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/notifications');

        $response->assertOk();
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->toDateString(),
            'status' => 'planned',
        ]);

        $user->notify(
            new ReadingPlanReminder($readingPlan, 'on_due_date')
        );

        $notification = $user->notifications()->first();

        $this->assertNull($notification->read_at);

        $response = $this
            ->actingAs($user)
            ->post("/notifications/{$notification->id}/read");

        $response->assertRedirect(route('notifications.index'));

        $notification->refresh();

        $this->assertNotNull($notification->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => now()->toDateString(),
            'status' => 'planned',
        ]);

        $owner->notify(
            new ReadingPlanReminder($readingPlan, 'on_due_date')
        );

        $notification = $owner->notifications()->first();

        $response = $this
            ->actingAs($otherUser)
            ->post("/notifications/{$notification->id}/read");

        $response->assertNotFound();

        $notification->refresh();

        $this->assertNull($notification->read_at);
    }

    public function test_command_creates_notification_for_due_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->toDateString(),
            'status' => 'planned',
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame('on_due_date', $notification->data['timing']);
    }

    public function test_command_does_not_create_duplicate_notification(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->toDateString(),
            'status' => 'planned',
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertSuccessful();

        $this->artisan('app:send-reading-plan-reminders')
            ->assertSuccessful();

        $this->assertSame(
            1,
            $user->notifications()
                ->where('data->timing', 'on_due_date')
                ->count()
        );
    }

    public function test_command_creates_notification_three_days_before_due_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'planned',
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame(
            'three_days_before',
            $notification->data['timing']
        );
    }

    public function test_command_creates_notification_three_days_after_due_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDays(3)->toDateString(),
            'status' => 'planned',
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame(
            'three_days_after',
            $notification->data['timing']
        );
    }

    public function test_command_does_not_notify_completed_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->toDateString(),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertSuccessful();

        $this->assertSame(
            0,
            $user->notifications()->count()
        );
    }
}
