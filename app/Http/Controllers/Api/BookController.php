<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexBookRequest;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Models\Book;

class BookController extends Controller
{
    public function index(IndexBookRequest $request)
    {
        $validated = $request->validated();

        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($validated['keyword'] ?? null, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%");
                });
            })
            ->when($validated['genre_id'] ?? null, function ($query, $genreId) {
                $query->whereHas('genres', function ($query) use ($genreId) {
                    $query->where('genres.id', $genreId);
                });
            })
            ->paginate($validated['per_page'] ?? 10);

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

        $validated['user_id'] = $request->user()->id;

        $validated['published_at'] = $validated['published_date'] ?? null;
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

        $validated['published_at'] = $validated['published_date'] ?? null;
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
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json([
            'message' => '書籍を削除しました。',
        ]);
    }
}
