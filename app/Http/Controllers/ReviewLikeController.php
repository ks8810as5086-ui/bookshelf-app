<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review)
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->likedReviews()->where('reviews.id', $review->id)->exists()) {
            $user->likedReviews()->detach($review->id);
        } else {
            $user->likedReviews()->attach($review->id);
        }

        return back();
    }
}
