<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%");
                });
            })
            ->when($request->genre_id, function ($query, $genreId) {
                $query->whereHas('genres', function ($query) use ($genreId) {
                    $query->where('genres.id', $genreId);
                });
            })
            ->paginate(10);

        return response()->json($books);
    }

    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews',
        ]);

        return response()->json($book);
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $validated['published_at'] = $validated['published_date'];
        unset($validated['published_date']);

        $genres = $validated['genres'];
        unset($validated['genres']);

        $book = Book::create($validated);

        $book->genres()->attach($genres);

        $book->load('genres');

        return response()->json($book, 201);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();

        $validated['published_at'] = $validated['published_date'];
        unset($validated['published_date']);

        $genres = $validated['genres'];
        unset($validated['genres']);

        $book->update($validated);

        $book->genres()->sync($genres);

        $book->load('genres');

        return response()->json($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'message' => '書籍を削除しました。',
        ]);
    }
}
