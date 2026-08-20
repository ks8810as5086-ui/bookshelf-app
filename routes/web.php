<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index'])
    ->name('books.index');

Route::get('/books/create', [BookController::class, 'create'])
    ->middleware('auth')
    ->name('books.create');

Route::get('/books/{book}', [BookController::class, 'show'])
    ->name('books.show');

Route::get('/ranking', function () {
    return view('ranking.index');
})->name('ranking.index');

Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])
    ->middleware('auth')
    ->name('favorites.toggle');

Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
    ->middleware('auth')
    ->name('reviews.store');

Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'toggle'])
    ->middleware('auth')
    ->name('reviews.like');

Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
    ->middleware('auth')
    ->name('reviews.edit');

Route::put('/reviews/{review}', [ReviewController::class, 'update'])
    ->middleware('auth')
    ->name('reviews.update');

Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
    ->middleware('auth')
    ->name('reviews.destroy');

Route::get('/books/{book}/edit', [BookController::class, 'edit'])
    ->middleware('auth')
    ->name('books.edit');

Route::put('/books/{book}', [BookController::class, 'update'])
    ->middleware('auth')
    ->name('books.update');

Route::delete('/books/{book}', [BookController::class, 'destroy'])
    ->middleware('auth')
    ->name('books.destroy');

Route::post('/books', [BookController::class, 'store'])
    ->middleware('auth')
    ->name('books.store');

Route::get('/favorites', [FavoriteController::class, 'index'])
    ->middleware('auth')
    ->name('favorites.index');
Route::get('/genres', [GenreController::class, 'index'])
    ->middleware('auth')
    ->name('genres.index');

Route::get('/genres/create', [GenreController::class, 'create'])
    ->middleware('auth')
    ->name('genres.create');

Route::post('/genres', [GenreController::class, 'store'])
    ->middleware('auth')
    ->name('genres.store');

Route::get('/genres/{genre}', [GenreController::class, 'show'])
    ->middleware('auth')
    ->name('genres.show');

Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])
    ->middleware('auth')
    ->name('genres.edit');

Route::put('/genres/{genre}', [GenreController::class, 'update'])
    ->middleware('auth')
    ->name('genres.update');

Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])
    ->middleware('auth')
    ->name('genres.destroy');
