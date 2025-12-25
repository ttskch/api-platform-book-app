<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ArticlePostProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Article
    {
        if (!$data instanceof Article) {
            throw new \InvalidArgumentException('このカスタムステートプロセッサーはArticleリソースに対してのみ使用可能です。');
        }

        $this->em->persist($data);
        $this->em->flush();

        $this->logger->info('ブログ記事が新規作成されました。');

        return $data;
    }
}
