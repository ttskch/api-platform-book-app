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
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiProperty(property: 'article', required: true)]
#[ApiProperty(property: 'content', required: true)]
class Comment extends Model
{
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
                    'article' => ['required'],
                    'content' => ['required'],
                ],
            ),
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
