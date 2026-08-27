<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $comments = [
            1 => 'あまり満足できる内容ではありませんでした。',
            2 => '期待していたほどではありませんでした。',
            3 => '良くも悪くもなく、普通に楽しめました。',
            4 => 'とても良い内容で、楽しんで読むことができました。',
            5 => '非常に満足できる内容で、ぜひおすすめしたい一冊です。',
        ];

        foreach ($books as $book) {
            $reviewCount = fake()->numberBetween(2, 4);

            $reviewUsers = $users->random($reviewCount);

            foreach ($reviewUsers as $user) {
                $rating = fake()->numberBetween(1, 5);

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating],
                ]);
            }
        }
    }
}
