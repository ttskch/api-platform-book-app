<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Models\Article;
use Psr\Log\LoggerInterface;

final class ArticlePostProcessor implements ProcessorInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Article
    {
        if (!$data instanceof Article) {
            throw new \InvalidArgumentException('このカスタムステートプロセッサーはArticleリソースに対してのみ使用可能です。');
        }

        $data->saveOrFail();
        $data->refresh();

        $this->logger->info('ブログ記事が新規作成されました。');

        return $data;
    }
}
