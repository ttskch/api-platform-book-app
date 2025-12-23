<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class Comment
{
    public function __construct(
        public int $id,
        public Article $article,
        public string $content,
    ) {
    }
}
