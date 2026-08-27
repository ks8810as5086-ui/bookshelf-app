<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Console\Command;

class UpdateOverdueReadingPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-overdue-reading-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '期日を過ぎた読書計画を期限超過に更新する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ReadingPlan::query()
            ->whereDate('target_date', '<', now()->toDateString())
            ->where('status', ReadingPlanStatus::Planned)
            ->update([
                'status' => ReadingPlanStatus::Overdue,
            ]);

        return self::SUCCESS;
    }
}
