<?php

/** @noinspection PhpNamedArgumentsWithChangedOrderInspection */

namespace App\Models;

use ApiPlatform\Laravel\Eloquent\Filter\BooleanFilter;
use ApiPlatform\Laravel\Eloquent\Filter\DateFilter;
use ApiPlatform\Laravel\Eloquent\Filter\OrderFilter;
use ApiPlatform\Laravel\Eloquent\Filter\PartialSearchFilter;
use ApiPlatform\Laravel\Eloquent\Filter\RangeFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Parameter as OpenApiParameter;
use App\ApiResource\Tag;
use App\Filter\Article\CrossoverSearchFilter;
use App\State\ArticleProcessor;
use App\State\ArticlePublishProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;
use RichanFongdasen\EloquentBlameable\BlameableTrait;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ApiProperty(
    property: 'id',
    serialize: new Groups(['article:read:item', 'article:read:list']),
)]
#[ApiProperty(
    property: 'title',
    required: true,
    serialize: new Groups(['article:read:item', 'article:read:list', 'article:write']),
)]
#[ApiProperty(
    property: 'content',
    serialize: new Groups(['article:read:item', 'article:read:list', 'article:write']),
)]
#[ApiProperty(
    property: 'published',
    description: '#required-on-read',
    serialize: new Groups(['article:read:item', 'article:read:list', 'article:write']),
)]
#[ApiProperty(
    property: 'comments',
    serialize: new Groups(['article:read:item']),
)]
#[ApiProperty(
    property: 'tags',
    schema: [
        'type' => 'array',
        'items' => [
            'type' => 'string',
            'enum' => Tag::ALLOWED_TAGS,
        ],
    ],
    serialize: new Groups(['article:read:item', 'article:read:list', 'article:write']),
)]
#[ApiProperty(
    property: 'relatedArticles',
    serialize: [
        new Groups(['article:read:item', 'article:write']),
        new MaxDepth(1),
    ],
)]
#[ApiProperty(
    property: 'popular',
    required: true,
    serialize: new Groups(['article:read:item', 'article:read:list']),
)]
#[ApiProperty(
    property: 'date',
    required: true,
    schema: [
        'type' => 'string',
        'format' => 'date',
        'example' => '2026-01-01',
    ],
    serialize: [
        new Groups(['article:read:item', 'article:read:list', 'article:write']),
        new Context(['datetime_format' => 'Y-m-d']),
    ],
)]
#[ApiProperty(
    property: 'image',
    serialize: [
        new Groups(['article:read:item', 'article:write']),
    ],
)]
#[ApiProperty(
    property: 'createdAt',
    required: true,
    schema: ['type' => 'string', 'format' => 'date-time'],
    serialize: new Groups(['article:read:item']),
)]
#[ApiProperty(
    property: 'updatedAt',
    required: true,
    schema: ['type' => 'string', 'format' => 'date-time'],
    serialize: new Groups(['article:read:item']),
)]
class Article extends Model
{
    use BlameableTrait;

    // API Platformではマスアサインメントは使用されないので $fillable の定義は不要
    // protected $fillable = [
    //     'title',
    //     'content',
    //     'published',
    //     'tags',
    //     'date',
    //     'image_id',
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

    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'article_article', 'article_source', 'article_target');
    }

    public function getPopularAttribute(): bool
    {
        return $this->comments()->count() >= 10;
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaObject::class);
    }

    public static function apiResource(): array
    {
        return [
            new ApiResource(
                rules: [
                    'title' => ['required', 'max:255'],
                    'tags' => ['array', 'nullable'],
                    'tags.*' => [Rule::in(Tag::ALLOWED_TAGS)],
                    'date' => ['required', 'date'],
                ],
                normalizationContext: ['groups' => ['article:read:item']],
                denormalizationContext: ['groups' => ['article:write']],
            ),
            new GetCollection(
                openapi: new Operation(summary: 'ブログ記事の一覧を取得する。'),
                normalizationContext: ['groups' => ['article:read:list']],
                parameters: [
                    new QueryParameter(
                        key: ':property',
                        filter: PartialSearchFilter::class,
                        properties: ['title'/* , 'comments.content' */], // ネストされたプロパティは未対応
                    ),

                    new QueryParameter(
                        key: 'date',
                        filter: DateFilter::class,
                    ),

                    new QueryParameter(
                        key: 'published',
                        filter: BooleanFilter::class,
                    ),

                    // new QueryParameter(
                    //     key: 'numeric[id]',
                    //     filter: NumericFilter::class, // 当該フィルターなし
                    // ),

                    new QueryParameter(
                        key: 'id',
                        filter: RangeFilter::class,
                    ),

                    // new QueryParameter(
                    //     key: 'exists[:property]',
                    //     filter: ExistsFilter::class,
                    //     properties: ['content', 'comments'], // 当該フィルターなし
                    // ),

                    new QueryParameter(
                        key: 'order[:property]',
                        filter: OrderFilter::class,
                        properties: ['id', 'date'],
                    ),

                    new QueryParameter(
                        key: 'query',
                        filter: CrossoverSearchFilter::class,
                        openApi: new OpenApiParameter(
                            name: 'query',
                            in: 'query',
                            description: 'ブログ記事のタイトルと本文を横断的に部分一致で検索する。',
                        ),
                    ),
                ],
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
                processor: ArticleProcessor::class,
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
