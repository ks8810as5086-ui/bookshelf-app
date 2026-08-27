<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_at,
            'description' => $this->description,
            'image_url' => $this->image_url,

            'genres' => $this->whenLoaded('genres', function () {
                return $this->genres->map(function ($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                    ];
                });
            }),

            'average_rating' => $this->when(
                isset($this->reviews_avg_rating),
                fn () => (float) $this->reviews_avg_rating
            ),

            'reviews_count' => $this->when(
                isset($this->reviews_count),
                fn () => $this->reviews_count
            ),

            'reviews' => ReviewResource::collection(
                $this->whenLoaded('reviews')
            ),
        ];
    }
}
