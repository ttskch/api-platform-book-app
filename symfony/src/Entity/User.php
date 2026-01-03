<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\Repository\UserRepository;
use App\State\UserMeProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_CLERK_USER_ID', fields: ['clerkUserId'])]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read:item'])]
    private ?string $clerkUserId = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClerkUserId(): ?string
    {
        return $this->clerkUserId;
    }

    public function setClerkUserId(string $clerkUserId): static
    {
        $this->clerkUserId = $clerkUserId;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->clerkUserId;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public static function apiResource(): array
    {
        return [
            new ApiResource(
                normalizationContext: ['groups' => ['user:read:item']],
            ),
            new Get(
                uriTemplate: '/users/me',
                openapi: new Operation(
                    summary: 'ログイン中のユーザー自身の詳細を取得する。',
                ),
                provider: UserMeProvider::class,
            ),
        ];
    }
}
