<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;

class StorePostData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        
        #[Required, Min(10)]
        public string $content,
        
        #[Required, Exists('users', 'id')]
        public int $user_id,
        
        public ?string $published_at = null,
    ) {}
}
