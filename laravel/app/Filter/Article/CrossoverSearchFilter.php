<?php

namespace App\Filter\Article;

use ApiPlatform\Laravel\Eloquent\Filter\FilterInterface;
use ApiPlatform\Metadata\Parameter;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;

class CrossoverSearchFilter implements FilterInterface
{
    public function apply(Builder $builder, mixed $values, Parameter $parameter, array $context = []): Builder
    {
        // このフィルターは Article エンティティの query プロパティに対してのみ有効
        if ($context['resource_class'] !== Article::class || $parameter->getKey() !== 'query') {
            return $builder;
        }

        // クエリパラメーターとして渡された値 $value を単一の文字列または null に変換
        $value = is_array($values) ? $values[0] : $values;
        $value = is_string($value) ? $value : null;

        // 検索文字列が与えられていない場合は何もしない
        if ($value === null) {
            return $builder;
        }

        // クエリビルダーに条件を追加
        return $builder->{$context['whereClause'] ?? 'where'}(fn (Builder $builder) => $builder
            ->where('title', 'like', '%'.str_replace('%', '\%', $value).'%')
            ->orWhere('content', 'like', '%'.str_replace('%', '\%', $value).'%')
        );
    }
}
