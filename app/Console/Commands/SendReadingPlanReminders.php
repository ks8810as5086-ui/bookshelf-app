<?php

namespace App\Console\Commands;

use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Console\Command;

class SendReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reading-plan-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の期日に応じてリマインド通知を送信する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->startOfDay();

        $reminders = [
            'three_days_before' => $today->copy()->addDays(3),
            'on_due_date' => $today,
            'three_days_after' => $today->copy()->subDays(3),
        ];

        foreach ($reminders as $timing => $targetDate) {
            $readingPlans = ReadingPlan::query()
                ->with(['user', 'book'])
                ->whereDate('target_date', $targetDate)
                ->where('status', 'planned')
                ->get();

            foreach ($readingPlans as $readingPlan) {
                $alreadyNotified = $readingPlan->user
                    ->notifications()
                    ->where('data->reading_plan_id', $readingPlan->id)
                    ->where('data->timing', $timing)
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }

                $readingPlan->user->notify(
                    new ReadingPlanReminder($readingPlan, $timing)
                );
            }
        }

        $this->info('読書計画のリマインド通知を送信しました。');

        return self::SUCCESS;
    }
}
