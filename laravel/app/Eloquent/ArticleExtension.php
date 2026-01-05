<?php

namespace App\Eloquent;

use ApiPlatform\Laravel\Eloquent\Extension\QueryExtensionInterface;
use ApiPlatform\Metadata\Operation;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ArticleExtension implements QueryExtensionInterface
{
    public function apply(Builder $builder, array $uriVariables, Operation $operation, $context = []): Builder
    {
        // Articleリソースのみを対象とする
        if (!$builder->getModel() instanceof Article) {
            return $builder;
        }

        $me = Auth::user();

        // 未ログイン状態なら公開記事のみアクセス可能
        if ($me === null) {
            return $builder->where('published', true);
        }

        // 管理者ならすべてアクセス可能
        if ($me->is_admin) {
            return $builder;
        }

        // ログイン済みなら公開記事および自分の記事のみアクセス可能
        return $builder->where(function (Builder $query) use ($me) {
            $query
                ->where('published', true)
                ->orWhere('created_by', $me->id)
            ;
        });
    }
}
