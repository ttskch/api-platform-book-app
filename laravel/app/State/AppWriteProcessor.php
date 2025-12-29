<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Models\Article;
use Psr\Log\LoggerInterface;

final class AppWriteProcessor implements ProcessorInterface
{
    public function __construct(
        private mixed $decorated,
        private LoggerInterface $logger,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        assert($this->decorated instanceof ProcessorInterface);

        $processed = $this->decorated->process($data, $operation, $uriVariables, $context);

        if ($data instanceof Article && $operation instanceof Post) {
            $this->logger->info('ブログ記事が新規作成されました。');
        }

        return $processed;
    }
}
