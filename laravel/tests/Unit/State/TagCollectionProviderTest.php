<?php

namespace Tests\Unit\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use App\ApiResource\Tag;
use App\State\TagCollectionProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TagCollectionProviderTest extends TestCase
{
    #[DataProvider('provideProvider')]
    public function testProvide(Operation $operation, array $context): void
    {
        $SUT = new TagCollectionProvider(new Pagination());

        $actual = $SUT->provide($operation, [], $context);

        self::assertIsIterable($actual);

        foreach ($actual as $i => $tag) {
            self::assertInstanceOf(Tag::class, $tag);
            self::assertSame($i + 1, $tag->id);
            self::assertSame(sprintf('tag%d', $i + 1), $tag->label);
        }
    }

    public static function provideProvider(): array
    {
        return [
            [new GetCollection(), []],
            [new GetCollection(), ['foo' => 'bar']],
        ];
    }
}
