<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Models\Article;

final class ArticlePublishProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Article
    {
        if (!$data instanceof Article) {
            throw new \InvalidArgumentException('このカスタムステートプロセッサーはArticleリソースに対してのみ使用可能です。');
        }

        $data->published = true;
        $data->saveOrFail();
        $data->refresh();

        return $data;
    }
}
