<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // 最初のユーザーを取得

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_at' => '1905-01-01',
                'description' => '猫の視点を通して、人間社会や知識人の姿を風刺的に描いた夏目漱石の長編小説。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={1}',
                'genres' => ['小説'],
            ],
            [

                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_at' => '1936-10-01',
                'description' => '人間関係を良好に築き、相手の心を動かすための考え方と実践方法をまとめた書籍。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={2}',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_at' => '2012-06-23',
                'description' => '読みやすく理解しやすいコードを書くための原則や具体的な改善方法を解説した技術書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={3}',
                'genres' => ['技術書'],
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_at' => '2013-08-30',
                'description' => '主体性や目標設定など、人生や仕事をより良くするための基本的な習慣を紹介した書籍。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={4}',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_at' => '1906-04-01',
                'description' => '東京から四国の中学校へ赴任した青年教師の奮闘を、ユーモアを交えて描いた小説。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={5}',
                'genres' => ['小説'],
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_at' => '2016-09-08',
                'description' => '人類の誕生から現代社会に至るまでの歴史を、科学や文化の視点から読み解いた書籍。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={6}',
                'genres' => ['歴史', '科学'],
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_at' => '2017-12-18',
                'description' => '保守しやすく品質の高いコードを書くための考え方や実践的な手法を解説した技術書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={7}',
                'genres' => ['技術書'],
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_at' => '2013-12-13',
                'description' => '他者からの評価に縛られず、自分らしく生きるための考え方を対話形式で解説した書籍。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={8}',
                'genres' => ['自己啓発'],
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_at' => '2015-03-11',
                'description' => 'お笑い芸人を目指す若者たちの葛藤や友情を通して、夢と現実を描いた小説。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={9}',
                'genres' => ['小説'],
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_at' => '2019-01-11',
                'description' => '思い込みを避け、客観的なデータをもとに世界を正しく理解するための考え方を紹介した書籍。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={10}',
                'genres' => ['ビジネス', '科学'],
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_at' => '2007-01-18',
                'description' => '海上輸送におけるコンテナの普及が、物流や世界経済に与えた影響を描いた書籍。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text={11}',
                'genres' => ['ビジネス', '歴史'],
            ],
        ];

        foreach ($books as $bookData) {
            $genreNames = $bookData['genres'];
            unset($bookData['genres']);

            $book = Book::firstOrCreate(
                ['isbn' => $bookData['isbn']],
                array_merge($bookData, [
                    'user_id' => $user->id,
                ])
            );

            $genreIds = Genre::whereIn('name', $genreNames)->pluck('id');

            $book->genres()->sync($genreIds);
        }
    }
}
