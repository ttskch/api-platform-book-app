<?php

/** @noinspection PhpNamedArgumentsWithChangedOrderInspection */

namespace App\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\State\MediaObjectPostProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiProperty(
    property: 'file',
    serialize: new Groups(['media_object:write']),
)]
#[ApiProperty(
    property: 'contentUrl',
    writable: false,
    required: true,
    types: ['https://schema.org/contentUrl'],
    serialize: new Groups(['media_object:read:item', 'article:read:item']),
)]
class MediaObject extends Model
{
    public $timestamps = false;

    // API Platformではマスアサインメントは使用されないので $fillable の定義は不要
    // protected $fillable = [
    //     'file_path',
    // ];

    protected $appends = [
        'file',
    ];

    public ?UploadedFile $file = null;

    public function getFileAttribute(): ?UploadedFile
    {
        return $this->file;
    }

    public function getContentUrlAttribute(): string
    {
        return url('/storage/'.$this->file_path);
    }

    public static function apiResource(): array
    {
        return [
            new ApiResource(
                types: ['https://schema.org/MediaObject'],
                rules: [
                    'file' => ['required', 'image'],
                ],
                normalizationContext: ['groups' => ['media_object:read:item']],
                denormalizationContext: ['groups' => ['media_object:write']],
                cacheHeaders: [
                    'shared_max_age' => 31536000, // 3600 * 24 * 365
                    'max_age' => 0,
                ],
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
                processor: MediaObjectPostProcessor::class,
            ),
        ];
    }
}
