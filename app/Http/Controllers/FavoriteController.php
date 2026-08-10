<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;

class FavoriteController extends Controller
{
    public function toggle(Book $book)
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->favoriteBooks()->where('books.id', $book->id)->exists()) {
            $user->favoriteBooks()->detach($book->id);
        } else {
            $user->favoriteBooks()->attach($book->id);
        }

        return back();
    }
}
