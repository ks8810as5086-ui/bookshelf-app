<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ReadingPlan $readingPlan,
        public string $timing,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = match ($this->timing) {
            'three_days_before' => '読書予定日の3日前です',
            'on_due_date' => '今日は読書予定日です',
            'three_days_after' => '読書予定日を過ぎています',
            default => '読書計画のお知らせ',
        };

        $bookTitle = $this->readingPlan->book->title;

        $body = match ($this->timing) {
            'three_days_before' => "「{$bookTitle}」の読書予定日まであと3日です。",
            'on_due_date' => "「{$bookTitle}」の読書予定日になりました。",
            'three_days_after' => "「{$bookTitle}」の読書予定日から3日が経過しました。",
            default => "「{$bookTitle}」の読書計画を確認してください。",
        };

        return [
            'reading_plan_id' => $this->readingPlan->id,
            'title' => $title,
            'body' => $body,
            'timing' => $this->timing,
        ];
    }
}
