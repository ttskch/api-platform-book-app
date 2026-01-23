<?php

/** @noinspection PhpNamedArgumentsWithChangedOrderInspection */

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\ApiResource\Tag;
use App\Repository\ArticleRepository;
use App\State\ArticlePublishProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(RangeFilter::class, properties: ['id'])]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article:read:item', 'article:read:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['article:read:item', 'article:read:list', 'article:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['article:read:item', 'article:read:list', 'article:write'])]
    private ?string $content = null;

    /**
     * #required-on-read
     */
    #[ORM\Column]
    #[Groups(['article:read:item', 'article:read:list', 'article:write'])]
    private bool $published = false;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'article', orphanRemoval: true)]
    #[Groups(['article:read:item'])]
    private Collection $comments;

    /**
     * @var array<string>
     */
    #[ORM\Column]
    #[Assert\Choice(choices: Tag::ALLOWED_TAGS, multiple: true)]
    #[Groups(['article:read:item', 'article:read:list', 'article:write'])]
    private array $tags = [];

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class)]
    #[Groups(['article:read:item', 'article:write'])]
    #[MaxDepth(1)]
    private Collection $relatedArticles;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Groups(['article:read:item', 'article:read:list', 'article:write'])]
    #[Context(['datetime_format' => 'Y-m-d'])]
    #[ApiProperty(
        required: true,
        schema: [
            'type' => 'string',
            'format' => 'date',
            'example' => '2026-01-01',
        ],
    )]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne]
    #[Groups(['article:read:item', 'article:write'])]
    private ?MediaObject $image = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->relatedArticles = new ArrayCollection();
    }

    #[Groups(['article:read:item', 'article:read:list'])]
    #[ApiProperty(required: true)]
    public function isPopular(): bool
    {
        return count($this->comments) >= 10;
    }

    #[Groups(['article:read:item'])]
    #[ApiProperty(
        required: true,
        schema: ['type' => 'string', 'format' => 'date-time'],
    )]
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    #[Groups(['article:read:item'])]
    #[ApiProperty(
        required: true,
        schema: ['type' => 'string', 'format' => 'date-time'],
    )]
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): static
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getRelatedArticles(): Collection
    {
        return $this->relatedArticles;
    }

    public function addRelatedArticle(self $relatedArticle): static
    {
        if (!$this->relatedArticles->contains($relatedArticle)) {
            $this->relatedArticles->add($relatedArticle);
        }

        return $this;
    }

    public function removeRelatedArticle(self $relatedArticle): static
    {
        $this->relatedArticles->removeElement($relatedArticle);

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getImage(): ?MediaObject
    {
        return $this->image;
    }

    public function setImage(?MediaObject $image): static
    {
        $this->image = $image;

        return $this;
    }

    public static function apiResource(): array
    {
        return [
            new ApiResource(
                normalizationContext: ['groups' => ['article:read:item']],
                denormalizationContext: ['groups' => ['article:write']],
                order: ['id' => 'ASC'],
                paginationViaCursor: [['field' => 'id', 'direction' => 'ASC']],
            ),
            new GetCollection(
                openapi: new Operation(summary: 'ブログ記事の一覧を取得する。'),
                normalizationContext: ['groups' => ['article:read:list']],
            ),
            new Post(
                openapi: new Operation(summary: 'ブログ記事を新規作成する。'),
            ),
            new Get(
                openapi: new Operation(
                    summary: '指定したブログ記事の詳細を取得する。',
                    parameters: [
                        new Parameter(
                            name: 'id',
                            in: 'path',
                            description: 'ブログ記事ID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
            ),
            new Delete(
                openapi: new Operation(
                    summary: '指定したブログ記事を削除する。',
                    parameters: [
                        new Parameter(
                            name: 'id',
                            in: 'path',
                            description: 'ブログ記事ID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
            ),
            new Patch(
                openapi: new Operation(
                    summary: '指定したブログ記事を更新する。',
                    parameters: [
                        new Parameter(
                            name: 'id',
                            in: 'path',
                            description: 'ブログ記事ID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
            ),
            new Put(
                uriTemplate: '/articles/{id}/publication',
                openapi: new Operation(
                    summary: '指定したブログ記事を公開済みにする。',
                    parameters: [
                        new Parameter(
                            name: 'id',
                            in: 'path',
                            description: 'ブログ記事ID',
                            required: true,
                            schema: ['type' => 'integer'],
                        ),
                    ],
                ),
                processor: ArticlePublishProcessor::class,
                deserialize: false,
            ),
        ];
    }
}
