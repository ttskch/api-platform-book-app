<?php

/** @noinspection PhpNamedArgumentsWithChangedOrderInspection */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\State\CreateProvider;
use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article:read:item'])]
    private ?int $id = null;

    /**
     * #required-on-read
     */
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comment:write:patch'])]
    private ?Article $article = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['comment:write', 'article:read:item'])]
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
            new ApiResource(
                denormalizationContext: ['groups' => ['comment:write']],
            ),
            new GetCollection(
                uriTemplate: '/articles/{articleId}/comments',
                uriVariables: [
                    'articleId' => new Link(
                        fromClass: Article::class,
                        toProperty: 'article',
                    ),
                ],
                openapi: new Operation(
                    summary: '指定したブログ記事に対するコメントの一覧を取得する。',
                    parameters: [
                        new Parameter(
                            name: 'articleId',
                            in: 'path',
                            description: 'ブログ記事ID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
            ),
            new Post(
                uriTemplate: '/articles/{articleId}/comments',
                uriVariables: [
                    'articleId' => new Link(
                        fromClass: Article::class,
                        toProperty: 'article',
                    ),
                ],
                openapi: new Operation(
                    summary: '指定したブログ記事に対するコメントを新規作成する。',
                    parameters: [
                        new Parameter(
                            name: 'articleId',
                            in: 'path',
                            description: 'ブログ記事ID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
                provider: CreateProvider::class,
                security: 'is_granted("ROLE_USER")',
            ),
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
                security: 'is_granted("EDIT", object)',
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
                denormalizationContext: ['groups' => ['comment:write', 'comment:write:patch']],
                security: 'is_granted("EDIT", object)',
            ),
        ];
    }
}
