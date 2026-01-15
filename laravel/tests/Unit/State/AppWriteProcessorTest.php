<?php

namespace Tests\Unit\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Models\Article;
use App\Models\Comment;
use App\State\AppWriteProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AppWriteProcessorTest extends TestCase
{
    #[DataProvider('processProvider')]
    public function testProcess(mixed $data, Operation $operation, array $uriVariables, array $context, bool $willBeLogged): void
    {
        $decorated = \Mockery::mock(ProcessorInterface::class)->makePartial();
        $decorated->shouldReceive('process')->with($data, $operation, $uriVariables, $context)->andReturn('expected');

        $logger = self::createMock(LoggerInterface::class);
        $logger->expects($this->exactly($willBeLogged ? 1 : 0))->method('info')->with('ブログ記事が新規作成されました。');

        $SUT = new AppWriteProcessor($decorated, $logger);

        $actual = $SUT->process($data, $operation, $uriVariables, $context);

        self::assertSame('expected', $actual);
    }

    public static function processProvider(): array
    {
        return [
            [new Article(), new Post(), [], [], true],
            [new Article(), new Post(), ['foo' => 'bar'], ['buz' => 'qux'], true],
            [new Comment(), new Post(), [], [], false],
            [new Article(), new Patch(), [], [], false],
        ];
    }
}
