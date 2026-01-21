<?php

namespace App\Data;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $created_at,
        /** @var ?DataCollection<PostData> */
        public ?DataCollection $posts = null,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            created_at: $user->created_at->format('Y-m-d H:i:s'),
            posts: $user->relationLoaded('posts') 
                ? PostData::collect($user->posts) 
                : null,
        );
    }
}
