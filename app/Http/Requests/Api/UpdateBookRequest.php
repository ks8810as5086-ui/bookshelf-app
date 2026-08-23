<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => [
                'required',
                'string',
                'size:13',
                'regex:/^[0-9]+$/',
                Rule::unique('books', 'isbn')
                    ->ignore($this->route('book')),
            ],
            'published_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:255',
            'genres' => 'required|array|min:1',
            'genres.*' => 'required|integer|exists:genres,id',
        ];
    }
}
