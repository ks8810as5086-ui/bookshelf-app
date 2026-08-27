<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_can_be_searched_by_title_keyword(): void
    {
        $user = User::factory()->create();

        Book::create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => 'PHP基礎',
            'author' => '鈴木花子',
        ]);

        $response = $this->get('/?keyword=Laravel');

        $response->assertOk();
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP基礎');
    }

    public function test_books_can_be_searched_by_author_keyword(): void
    {
        $user = User::factory()->create();

        Book::create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => 'PHP基礎',
            'author' => '鈴木花子',
        ]);

        $response = $this->get('/?keyword=鈴木');

        $response->assertOk();
        $response->assertSee('PHP基礎');
        $response->assertDontSee('Laravel入門');
    }

    public function test_books_can_be_filtered_by_genre(): void
    {
        $user = User::factory()->create();

        $technicalGenre = Genre::create([
            'name' => '技術書',
        ]);

        $novelGenre = Genre::create([
            'name' => '小説',
        ]);

        $technicalBook = Book::create([
            'user_id' => $user->id,
            'title' => '技術書テスト',
            'author' => '著者A',
        ]);

        $novelBook = Book::create([
            'user_id' => $user->id,
            'title' => '小説テスト',
            'author' => '著者B',
        ]);

        $technicalBook->genres()->attach($technicalGenre->id);
        $novelBook->genres()->attach($novelGenre->id);

        $response = $this->get("/?genre={$technicalGenre->id}");

        $response->assertOk();
        $response->assertSee('技術書テスト');
        $response->assertDontSee('小説テスト');
    }

    public function test_keyword_and_genre_filter_can_be_combined(): void
    {
        $user = User::factory()->create();

        $technicalGenre = Genre::create([
            'name' => '技術書',
        ]);

        $novelGenre = Genre::create([
            'name' => '小説',
        ]);

        $matchingBook = Book::create([
            'user_id' => $user->id,
            'title' => 'Laravel実践',
            'author' => '著者A',
        ]);

        $differentGenreBook = Book::create([
            'user_id' => $user->id,
            'title' => 'Laravel物語',
            'author' => '著者B',
        ]);

        $differentKeywordBook = Book::create([
            'user_id' => $user->id,
            'title' => 'PHP実践',
            'author' => '著者C',
        ]);

        $matchingBook->genres()->attach($technicalGenre->id);
        $differentGenreBook->genres()->attach($novelGenre->id);
        $differentKeywordBook->genres()->attach($technicalGenre->id);

        $response = $this->get(
            "/?keyword=Laravel&genre={$technicalGenre->id}"
        );

        $response->assertOk();
        $response->assertSee('Laravel実践');
        $response->assertDontSee('Laravel物語');
        $response->assertDontSee('PHP実践');
    }

    public function test_pagination_links_keep_search_conditions(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '技術書',
        ]);

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::create([
                'user_id' => $user->id,
                'title' => "Laravel書籍{$i}",
                'author' => 'テスト著者',
            ]);

            $book->genres()->attach($genre->id);
        }

        $response = $this->get(
            "/?keyword=Laravel&genre={$genre->id}&sort=title"
        );

        $response->assertOk();

        $response->assertSee(
            'keyword=Laravel',
            false
        );

        $response->assertSee(
            "genre={$genre->id}",
            false
        );

        $response->assertSee(
            'sort=title',
            false
        );
    }

    public function test_default_sort_is_latest(): void
    {
        $user = User::factory()->create();

        $oldBook = Book::create([
            'user_id' => $user->id,
            'title' => '古い書籍',
            'author' => '著者A',
        ]);

        $oldBook->created_at = now()->subDay();
        $oldBook->updated_at = now()->subDay();
        $oldBook->save();

        $newBook = Book::create([
            'user_id' => $user->id,
            'title' => '新しい書籍',
            'author' => '著者B',
        ]);

        $newBook->created_at = now();
        $newBook->updated_at = now();
        $newBook->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeInOrder([
            '新しい書籍',
            '古い書籍',
        ]);
    }

    public function test_books_can_be_sorted_by_oldest(): void
    {
        $user = User::factory()->create();

        $oldBook = Book::create([
            'user_id' => $user->id,
            'title' => '古い書籍',
            'author' => '著者A',
        ]);

        $oldBook->created_at = now()->subDay();
        $oldBook->updated_at = now()->subDay();
        $oldBook->save();

        $newBook = Book::create([
            'user_id' => $user->id,
            'title' => '新しい書籍',
            'author' => '著者B',
        ]);

        $newBook->created_at = now();
        $newBook->updated_at = now();
        $newBook->save();

        $response = $this->get('/?sort=oldest');

        $response->assertOk();
        $response->assertSeeInOrder([
            '古い書籍',
            '新しい書籍',
        ]);
    }

    public function test_books_can_be_sorted_by_title(): void
    {
        $user = User::factory()->create();

        Book::create([
            'user_id' => $user->id,
            'title' => 'B書籍',
            'author' => '著者B',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => 'A書籍',
            'author' => '著者A',
        ]);

        $response = $this->get('/?sort=title');

        $response->assertOk();
        $response->assertSeeInOrder([
            'A書籍',
            'B書籍',
        ]);
    }

    public function test_books_can_be_sorted_by_average_rating(): void
    {
        $user = User::factory()->create();

        $highRatedBook = Book::create([
            'user_id' => $user->id,
            'title' => '高評価書籍',
            'author' => '著者A',
        ]);

        $lowRatedBook = Book::create([
            'user_id' => $user->id,
            'title' => '低評価書籍',
            'author' => '著者B',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => 'レビューなし書籍',
            'author' => '著者C',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 5,
            'comment' => '高評価',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
            'comment' => '低評価',
        ]);

        $response = $this->get('/?sort=rating');

        $response->assertOk();
        $response->assertSeeInOrder([
            '高評価書籍',
            '低評価書籍',
            'レビューなし書籍',
        ]);
    }
}
