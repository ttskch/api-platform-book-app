<?php

/** @noinspection PhpNamedArgumentsWithChangedOrderInspection */

namespace App\Entity;

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
use App\State\ArticlePostProcessor;
use App\State\ArticlePublishProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article:read:item', 'article:read:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['article:read:item', 'article:read:list'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['article:read:item', 'article:read:list'])]
    private ?string $content = null;

    /**
     * #required-on-read
     */
    #[ORM\Column]
    #[Groups(['article:read:item', 'article:read:list'])]
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
    #[Groups(['article:read:item', 'article:read:list'])]
    private array $tags = [];

    public function __construct()
    {
        $this->comments = new ArrayCollection();
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

    public static function apiResource(): array
    {
        return [
            new ApiResource(normalizationContext: ['groups' => ['article:read:item']]),
            new GetCollection(
                openapi: new Operation(summary: 'ブログ記事の一覧を取得する。'),
                normalizationContext: ['groups' => ['article:read:list']],
            ),
            new Post(
                openapi: new Operation(summary: 'ブログ記事を新規作成する。'),
                processor: ArticlePostProcessor::class,
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
