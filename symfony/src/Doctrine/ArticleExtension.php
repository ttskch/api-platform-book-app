<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Article;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ArticleExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $qb, string $resourceClass): void
    {
        // Articleリソースのみを対象とする
        if ($resourceClass !== Article::class) {
            return;
        }

        // 管理者に対しては何もしない
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        // クエリビルダーに条件を追加するための準備として
        // テーブル名のエイリアスを取得
        $alias = $qb->getRootAliases()[0];

        // テーブル名のエイリアスを使用してクエリビルダーに条件を追加
        $qb
            ->andWhere($qb->expr()->orX(
                sprintf('%s.published = true', $alias),
                sprintf('%s.createdBy = :currentClerkUserId', $alias),
            ))
            ->setParameter('currentClerkUserId', $this->security->getUser()?->getUserIdentifier())
        ;
    }
}
