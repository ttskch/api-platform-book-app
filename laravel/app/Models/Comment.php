<?php

/** @noinspection PhpNamedArgumentsWithChangedOrderInspection */

namespace App\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\State\CommentCreateProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RichanFongdasen\EloquentBlameable\BlameableTrait;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiProperty(
    property: 'id',
    serialize: new Groups(['article:read:item']),
)]
#[ApiProperty(
    property: 'article',
    description: '#required-on-read',
    required: true,
    serialize: new Groups(['comment:write:patch']),
)]
#[ApiProperty(
    property: 'content',
    required: true,
    serialize: new Groups(['comment:write', 'article:read:item']),
)]
class Comment extends Model
{
    use BlameableTrait;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    public $timestamps = false;

    // API Platformではマスアサインメントは使用されないので $fillable の定義は不要
    // protected $fillable = [
    //     'article_id',
    //     'content',
    // ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public static function apiResource(): array
    {
        return [
            new ApiResource(
                rules: [
                    'content' => ['required'],
                ],
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
                provider: CommentCreateProvider::class,
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
            ),
        ];
    }
}
