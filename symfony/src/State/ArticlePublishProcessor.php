<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

class ArticlePublishProcessor implements ProcessorInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Article
    {
        if (!$data instanceof Article) {
            throw new \InvalidArgumentException('このカスタムステートプロセッサーはArticleリソースに対してのみ使用可能です。');
        }

        $data->setPublished(true);
        $this->em->flush();

        return $data;
    }
}
