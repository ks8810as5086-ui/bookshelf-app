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
        $users = User::pluck('id', 'email');
        $books = Book::pluck('id', 'isbn');

        $reviews = [
            // 吾輩は猫である（3件）
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784101010014'],
                'rating' => 5,
                'comment' => '猫の視点から描かれる物語がとても新鮮でした。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784101010014'],
                'rating' => 4,
                'comment' => 'ユーモアがあり、最後まで楽しく読むことができました。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784101010014'],
                'rating' => 5,
                'comment' => '何度読んでも味わい深い名作だと思います。',
            ],

            // 人を動かす（3件）
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784422100524'],
                'rating' => 5,
                'comment' => '人との接し方を改めて考えさせられる一冊でした。',
            ],
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784422100524'],
                'rating' => 4,
                'comment' => '仕事にも日常にも役立つ内容でした。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784422100524'],
                'rating' => 5,
                'comment' => 'コミュニケーションの基本を学べました。',
            ],

            // リーダブルコード（3件）
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784873115658'],
                'rating' => 5,
                'comment' => 'エンジニアなら一度は読むべき内容です。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784873115658'],
                'rating' => 4,
                'comment' => 'コードを書く意識が変わりました。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784873115658'],
                'rating' => 5,
                'comment' => '具体例が多く理解しやすかったです。',
            ],

            // 7つの習慣（3件）
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784863940246'],
                'rating' => 5,
                'comment' => '人生観が変わるきっかけになりました。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784863940246'],
                'rating' => 4,
                'comment' => '仕事にも活かせる考え方が多くありました。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784863940246'],
                'rating' => 5,
                'comment' => '繰り返し読み返したくなる内容です。',
            ],

            // 坊ちゃん（2件）
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784101010021'],
                'rating' => 4,
                'comment' => '主人公のまっすぐな性格が魅力的でした。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784101010021'],
                'rating' => 5,
                'comment' => 'テンポが良く読みやすい作品でした。',
            ],

            // サピエンス全史（2件）
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784309226712'],
                'rating' => 5,
                'comment' => '歴史の見方が大きく変わる一冊でした。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784309226712'],
                'rating' => 4,
                'comment' => '非常に読み応えがあり、多くの学びがありました。',
            ],

            // Clean Code（3件）
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784048930598'],
                'rating' => 5,
                'comment' => '読みやすく保守しやすいコードを書くための考え方を学べました。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784048930598'],
                'rating' => 4,
                'comment' => '日々の開発で意識したい実践的な内容が多くありました。',
            ],
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784048930598'],
                'rating' => 5,
                'comment' => 'コードの品質について深く考えるきっかけになりました。',
            ],

            // 嫌われる勇気（3件）
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784478025819'],
                'rating' => 5,
                'comment' => '他者の評価に縛られない生き方について考えさせられました。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784478025819'],
                'rating' => 4,
                'comment' => '対話形式で書かれているため、内容を理解しやすかったです。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784478025819'],
                'rating' => 5,
                'comment' => '自分の課題と他者の課題を分ける考え方が印象に残りました。',
            ],

            // 火花（3件）
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784163902302'],
                'rating' => 4,
                'comment' => '夢を追う若者の葛藤が丁寧に描かれていました。',
            ],
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784163902302'],
                'rating' => 5,
                'comment' => '登場人物の関係性と芸人としての生き方が心に残りました。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784163902302'],
                'rating' => 4,
                'comment' => '理想と現実の間で悩む姿に引き込まれました。',
            ],

            // FACTFULNESS（3件）
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784822289607'],
                'rating' => 5,
                'comment' => 'データをもとに世界を見ることの大切さを学びました。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784822289607'],
                'rating' => 5,
                'comment' => '自分の思い込みに気付かされる内容が多くありました。',
            ],
            [
                'user_id' => $users['tanaka@example.com'],
                'book_id' => $books['9784822289607'],
                'rating' => 4,
                'comment' => '具体的な統計が紹介されており、説得力のある一冊でした。',
            ],

            // コンテナ物語（4件）
            [
                'user_id' => $users['takahashi@example.com'],
                'book_id' => $books['9784822251468'],
                'rating' => 5,
                'comment' => 'コンテナが世界の物流を変えた過程がよく分かりました。',
            ],
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784822251468'],
                'rating' => 4,
                'comment' => '身近な物流の背景にある歴史を興味深く読めました。',
            ],
            [
                'user_id' => $users['sato@example.com'],
                'book_id' => $books['9784822251468'],
                'rating' => 5,
                'comment' => '技術革新が経済や社会に与える影響を理解できました。',
            ],
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784822251468'],
                'rating' => 4,
                'comment' => '物流の仕組みと世界経済のつながりが分かりやすかったです。',
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
