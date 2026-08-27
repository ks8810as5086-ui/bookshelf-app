<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        Review::all()->each(function (Review $review) use ($users) {
            $likeCount = fake()->numberBetween(0, 3);

            if ($likeCount === 0) {
                return;
            }

            $likeUserIds = $users
                ->where('id', '!=', $review->user_id)
                ->random($likeCount)
                ->pluck('id')
                ->all();

            $review->likedByUsers()
                ->syncWithoutDetaching($likeUserIds);
        });
    }
}
