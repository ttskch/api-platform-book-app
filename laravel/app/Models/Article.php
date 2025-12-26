<?php

/** @noinspection PhpNamedArgumentsWithChangedOrderInspection */

namespace App\Models;

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
use App\State\ArticlePostProcessor;
use App\State\ArticlePublishProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

#[ApiProperty(property: 'title', required: true)]
#[ApiProperty(
    property: 'tags',
    schema: [
        'type' => 'array',
        'items' => [
            'type' => 'string',
            'enum' => Tag::ALLOWED_TAGS,
        ],
    ],
)]
class Article extends Model
{
    public $timestamps = false;

    // API Platformではマスアサインメントは使用されないので $fillable の定義は不要
    // protected $fillable = [
    //     'title',
    //     'content',
    //     'published',
    //     'tags',
    // ];

    protected $casts = [
        'published' => 'boolean',
        'tags' => 'array',
    ];

    protected $attributes = [
        'published' => false,
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public static function apiResource(): array
    {
        return [
            new ApiResource(
                rules: [
                    'title' => ['required', 'max:255'],
                    'tags' => ['array', 'nullable'],
                    'tags.*' => [Rule::in(Tag::ALLOWED_TAGS)],
                ],
            ),
            new GetCollection(openapi: new Operation(summary: 'ブログ記事の一覧を取得する。')),
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
