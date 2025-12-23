<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class Article
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $content = null,
        public bool $published = false,
    ) {
    }
}
