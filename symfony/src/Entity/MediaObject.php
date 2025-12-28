<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\Repository\MediaObjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MediaObjectRepository::class)]
class MediaObject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1000)]
    private ?string $filePath = null;

    #[Vich\UploadableField(
        mapping: 'media_object',
        fileNameProperty: 'filePath',
    )]
    #[Assert\NotNull]
    #[Assert\File(mimeTypes: ['image/*'])]
    public ?File $file = null;

    #[ApiProperty(
        writable: false,
        required: true,
        types: ['https://schema.org/contentUrl'],
    )]
    #[Groups(['media_object:read:item', 'article:read:item'])]
    public ?string $contentUrl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public static function apiResource(): array
    {
        return [
            new ApiResource(
                types: ['https://schema.org/MediaObject'],
                normalizationContext: ['groups' => ['media_object:read:item']],
            ),
            new Get(),
            new Post(
                inputFormats: ['multipart' => ['multipart/form-data']],
                openapi: new Operation(
                    requestBody: new RequestBody(
                        content: new \ArrayObject([
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'file' => [
                                            'type' => 'string',
                                            'format' => 'binary',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ),
            ),
        ];
    }
}
