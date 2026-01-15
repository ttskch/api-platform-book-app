<?php

namespace Tests\Unit\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use App\Models\Article;
use App\Models\Comment;
use App\State\ArticlePublishProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArticlePublishProcessorTest extends TestCase
{
    #[DataProvider('processProvider')]
    public function testProcess(string $modelClass, Operation $operation, array $uriVariables, array $context, bool $isSupported): void
    {
        $data = \Mockery::mock($modelClass)->makePartial();
        $data->shouldReceive('saveOrFail')->withNoArgs();

        $SUT = new ArticlePublishProcessor();

        if (!$isSupported) {
            self::expectExceptionMessage('このカスタムステートプロセッサーはArticleリソースに対してのみ使用可能です。');
        }

        $actual = $SUT->process($data, $operation, $uriVariables, $context);

        self::assertTrue($actual->getAttribute('published'));
    }

    public static function processProvider(): array
    {
        return [
            [Article::class, new Put(), [], [], true],
            [Article::class, new Put(), ['foo' => 'bar'], ['buz' => 'qux'], true],
            [Comment::class, new Put(), [], [], false],
        ];
    }
}
