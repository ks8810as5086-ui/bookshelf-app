<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();

        $yamada = User::where('email', 'yamada@example.com')->firstOrFail();
        $suzuki = User::where('email', 'suzuki@example.com')->firstOrFail();

        $books = Book::orderBy('id')->get();

        $plans = [
            // 山田太郎：3日後 → three_days_before リマインダー対象
            [
                'user_id' => $yamada->id,
                'book_id' => $books[0]->id,
                'target_date' => $today->copy()->addDays(3),
                'status' => ReadingPlanStatus::Planned,
                'completed_at' => null,
            ],

            // 山田太郎：今日 → on_due_date リマインダー対象
            [
                'user_id' => $yamada->id,
                'book_id' => $books[1]->id,
                'target_date' => $today->copy(),
                'status' => ReadingPlanStatus::Planned,
                'completed_at' => null,
            ],

            // 山田太郎：3日前 → 期限超過状態
            [
                'user_id' => $yamada->id,
                'book_id' => $books[2]->id,
                'target_date' => $today->copy()->subDays(3),
                'status' => ReadingPlanStatus::Planned,
                'completed_at' => null,
            ],

            // 山田太郎：期限超過状態の確認用
            [
                'user_id' => $yamada->id,
                'book_id' => $books[7]->id,
                'target_date' => $today->copy()->subDays(10),
                'status' => ReadingPlanStatus::Overdue,
                'completed_at' => null,
            ],
            // 山田太郎：通常の読書予定 → リマインダー対象外
            [
                'user_id' => $yamada->id,
                'book_id' => $books[3]->id,
                'target_date' => $today->copy()->addDays(10),
                'status' => ReadingPlanStatus::Planned,
                'completed_at' => null,
            ],

            // 山田太郎：読了済み
            [
                'user_id' => $yamada->id,
                'book_id' => $books[4]->id,
                'target_date' => $today->copy()->subDays(5),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => $today->copy()->subDays(6),
            ],

            // 鈴木花子：別ユーザーの予定 → 認可確認用
            [
                'user_id' => $suzuki->id,
                'book_id' => $books[5]->id,
                'target_date' => $today->copy()->addDays(7),
                'status' => ReadingPlanStatus::Planned,
                'completed_at' => null,
            ],

            // 鈴木花子：別ユーザーの期限超過
            [
                'user_id' => $suzuki->id,
                'book_id' => $books[6]->id,
                'target_date' => $today->copy()->subDay(),
                'status' => ReadingPlanStatus::Overdue,
                'completed_at' => null,
            ],
        ];

        foreach ($plans as $plan) {
            ReadingPlan::updateOrCreate(
                [
                    'user_id' => $plan['user_id'],
                    'book_id' => $plan['book_id'],
                ],
                [
                    'target_date' => $plan['target_date'],
                    'status' => $plan['status'],
                    'completed_at' => $plan['completed_at'],
                ]
            );
        }
    }
}
