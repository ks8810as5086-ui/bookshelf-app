<?php

use App\Http\Controllers\BookController;
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

Route::get('/favorites', function () {
    return view('favorites.index');
})->middleware('auth')->name('favorites.index');

Route::get('/genres', function () {
    return view('genres.index');
})->middleware('auth')->name('genres.index');
