<?php

namespace App\Data;

use App\Models\Post;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class PostData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public ?string $excerpt,
        public ?string $published_at,
        public bool $is_published,
        public Lazy|UserData|null $user,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(Post $post): self
    {
        return new self(
            id: $post->id,
            title: $post->title,
            content: $post->content,
            excerpt: strlen($post->content) > 100 
                ? substr($post->content, 0, 100) . '...' 
                : null,
            published_at: $post->published_at?->format('Y-m-d H:i:s'),
            is_published: $post->published_at !== null && $post->published_at->isPast(),
            user: Lazy::whenLoaded(
                'user',
                $post,
                fn() => UserData::fromModel($post->user)
            ),
            created_at: $post->created_at->format('Y-m-d H:i:s'),
            updated_at: $post->updated_at->format('Y-m-d H:i:s'),
        );
    }
}
