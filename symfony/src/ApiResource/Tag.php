<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class Tag
{
    public const array ALLOWED_TAGS = [
        'tag1',
        'tag2',
        'tag3',
        'tag4',
        'tag5',
        'tag6',
        'tag7',
        'tag8',
        'tag9',
        'tag10',
    ];

    public function __construct(
        public int $id,
        public string $label,
    ) {
    }
}
