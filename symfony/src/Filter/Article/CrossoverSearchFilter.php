<?php

namespace App\Filter\Article;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Article;
use Doctrine\ORM\QueryBuilder;

class CrossoverSearchFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        // このフィルターは Article エンティティの query プロパティに対してのみ有効
        if ($resourceClass !== Article::class || $property !== 'query') {
            return;
        }

        // クエリパラメーターとして渡された値 $value を単一の文字列または null に変換
        $value = is_array($value) ? $value[0] : $value;
        $value = is_string($value) ? $value : null;

        // 検索文字列が与えられていない場合は何もしない
        if ($value === null) {
            return;
        }

        // クエリビルダーに条件を追加するための準備として
        // テーブル名のエイリアスを取得、一意なパラメーター名を生成
        $alias = $queryBuilder->getRootAliases()[0];
        $parameter = $queryNameGenerator->generateParameterName('query');

        // テーブル名のエイリアスとパラメーター名を使用してクエリビルダーに条件を追加
        $queryBuilder
            ->andWhere($queryBuilder->expr()->orX(
                sprintf('%s.title LIKE :%s', $alias, $parameter),
                sprintf('%s.content LIKE :%s', $alias, $parameter),
            ))
            ->setParameter($parameter, '%'.str_replace('%', '\%', $value).'%')
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'query' => [
                'property' => 'freetextQuery',
                'required' => false,
                'type' => 'string',
                'description' => 'ブログ記事のタイトルと本文を横断的に部分一致で検索する。',
            ],
        ];
    }
}
