<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => [
                'nullable',
                'string',
                'size:13',
                'regex:/^[0-9]+$/',
                Rule::unique('books', 'isbn'),
            ],
            'published_date' => ['nullable', 'date'],
            'description' => 'nullable|string|max:1000',
            'image_url' => ['nullable', 'url', 'max:255'],
            'genres' => 'required|array|min:1',
            'genres.*' => 'required|integer|exists:genres,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名を入力してください。',
            'author.string' => '著者名は文字列で入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.string' => 'ISBNは文字列で入力してください。',
            'isbn.size' => 'ISBNは13桁で入力してください。',
            'isbn.regex' => 'ISBNは数字のみで入力してください。',
            'isbn.unique' => 'このISBNは既に登録されています。',
            'published_date.date' => '出版日は有効な日付で入力してください。',
            'description.string' => '説明は文字列で入力してください。',
            'description.max' => '説明は1000文字以内で入力してください。',
            'image_url.url' => '画像URLは有効なURL形式で入力してください。',
            'image_url.max' => '画像URLは255文字以内で入力してください。',
            'genres.required' => 'ジャンルを1つ以上選択してください。',
            'genres.array' => 'ジャンルの形式が正しくありません。',
            'genres.min' => 'ジャンルを1つ以上選択してください。',
            'genres.*.integer' => 'ジャンルIDは整数で指定してください。',
            'genres.*.exists' => '選択されたジャンルは存在しません。',
        ];
    }
}
