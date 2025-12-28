<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Article;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'api_platform.state_processor.write')]
class AppWriteProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private LoggerInterface $logger,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $processed = $this->decorated->process($data, $operation, $uriVariables, $context);

        if ($data instanceof Article && $operation instanceof Post) {
            $this->logger->info('ブログ記事が新規作成されました。');
        }

        return $processed;
    }
}
