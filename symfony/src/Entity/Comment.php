<?php

namespace App\Entity;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article:read:item'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Article $article = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['article:read:item'])]
    private ?string $content = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public static function apiResource(): array
    {
        return [
            new GetCollection(openapi: new Operation(summary: 'コメントの一覧を取得する。')),
            new Post(openapi: new Operation(summary: 'コメントを新規作成する。')),
            new Get(
                openapi: new Operation(
                    summary: '指定したコメントの詳細を取得する。',
                    parameters: [
                        new Parameter(
                            name: 'id',
                            in: 'path',
                            description: 'コメントID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
            ),
            new Delete(
                openapi: new Operation(
                    summary: '指定したコメントを削除する。',
                    parameters: [
                        new Parameter(
                            name: 'id',
                            in: 'path',
                            description: 'コメントID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
            ),
            new Patch(
                openapi: new Operation(
                    summary: '指定したコメントを更新する。',
                    parameters: [
                        new Parameter(
                            name: 'id',
                            in: 'path',
                            description: 'コメントID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
            ),
        ];
    }
}
