<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Models\Article;

final class ArticleProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Article
    {
        if (!$data instanceof Article) {
            throw new \InvalidArgumentException('このカスタムステートプロセッサーはArticleリソースに対してのみ使用可能です。');
        }

        // related_articles フィールドの値を退避
        $relatedArticles = $data->related_articles;

        // related_articles フィールドを除去
        $attributes = $data->getAttributes();
        unset($attributes['related_articles']);
        $data->setRawAttributes($attributes);

        $data->saveOrFail();

        // related_articles フィールドの値を中間テーブルに同期
        if ($relatedArticles !== null) {
            $ids = array_column($relatedArticles, 'id');
            $data->relatedArticles()->sync($ids);
        }

        $data->refresh();

        return $data;
    }
}
