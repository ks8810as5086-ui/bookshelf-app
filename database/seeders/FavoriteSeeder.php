<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();

        User::all()->each(function (User $user) use ($books) {
            $favoriteBookIds = $books
                ->random(fake()->numberBetween(3, 5))
                ->pluck('id')
                ->all();

            $user->favoriteBooks()
                ->syncWithoutDetaching($favoriteBookIds);
        });
    }
}
