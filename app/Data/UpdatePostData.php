<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdatePostData extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional $title,
        
        #[Min(10)]
        public string|Optional $content,
        
        #[Exists('users', 'id')]
        public int|Optional $user_id,
        
        public string|Optional|null $published_at = null,
    ) {}
}
