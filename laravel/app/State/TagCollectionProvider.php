<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Tag;

final class TagCollectionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return array_map(
            fn (string $tag, int $i) => new Tag($i + 1, $tag),
            Tag::ALLOWED_TAGS,
            array_keys(Tag::ALLOWED_TAGS),
        );
    }
}
