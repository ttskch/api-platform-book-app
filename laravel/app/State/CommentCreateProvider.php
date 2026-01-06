<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Models\Article;
use App\Models\Comment;

final class CommentCreateProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $article = Article::findOrFail($uriVariables['articleId']);

        $comment = new Comment();
        $comment->article = $article;

        return $comment;
    }
}
