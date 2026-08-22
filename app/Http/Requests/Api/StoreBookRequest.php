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
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => [
                'required',
                'string',
                'size:13',
                'regex:/^[0-9]+$/',
                Rule::unique('books', 'isbn'),
            ],
            'published_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:255',
            'genres' => 'required|array|min:1',
            'genres.*' => 'required|integer|exists:genres,id',
        ];
    }
}
