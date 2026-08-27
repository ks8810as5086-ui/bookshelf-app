<?php

namespace App\Http\Controllers;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $reviews = $user->reviews()
            ->with('book.genres')
            ->get();

        $ratingDistribution = collect(range(1, 5))
            ->map(function ($rating) use ($reviews) {
                return $reviews->where('rating', $rating)->count();
            });
        $topRatedBooks = $reviews
            ->filter(function ($review) {
                return $review->rating >= 4;
            })
            ->sortByDesc('rating')
            ->take(5)
            ->values()
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            });
        $genreReviews = $reviews->flatMap(function ($review) {
            return $review->book->genres->map(function ($genre) use ($review) {
                return [
                    'genre_id' => $genre->id,
                    'genre_name' => $genre->name,
                    'rating' => $review->rating,
                ];
            });
        });
        $genreGroups = $genreReviews->groupBy('genre_id');
        $genreRatings = $genreGroups->map(function ($items) {
            return [
                'id' => $items->first()['genre_id'],
                'name' => $items->first()['genre_name'],
                'count' => $items->count(),
                'average_rating' => $items->avg('rating'),
            ];
        })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();
        $stats = [
            'summary' => [
                'total_reviews' => $reviews->count(),
                'books_read' => $reviews->count(),
                'average_rating' => $reviews->avg('rating') ?? 0,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
