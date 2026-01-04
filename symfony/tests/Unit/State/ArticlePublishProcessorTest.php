<?php

namespace App\Tests\Unit\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use App\Entity\Article;
use App\Entity\Comment;
use App\State\ArticlePublishProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArticlePublishProcessorTest extends TestCase
{
    #[DataProvider('processProvider')]
    public function testProcess(mixed $data, Operation $operation, array $uriVariables, array $context, bool $isSupported): void
    {
        $em = self::createMock(EntityManagerInterface::class);
        $em->expects($this->exactly($isSupported ? 1 : 0))->method('flush');

        $SUT = new ArticlePublishProcessor($em);

        if (!$isSupported) {
            self::expectExceptionMessage('このカスタムステートプロセッサーはArticleリソースに対してのみ使用可能です。');
        }

        $actual = $SUT->process($data, $operation, $uriVariables, $context);

        self::assertTrue($actual->isPublished());
    }

    public static function processProvider(): array
    {
        return [
            [new Article(), new Put(), [], [], true],
            [new Article(), new Put(), ['foo' => 'bar'], ['buz' => 'qux'], true],
            [new Comment(), new Put(), [], [], false],
        ];
    }
}
