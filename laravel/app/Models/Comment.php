<?php

namespace App\Models;

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

#[ApiResource(
    rules: [
        'article' => ['required'],
        'content' => ['required'],
    ],
)]
#[GetCollection(openapi: new Operation(summary: 'コメントの一覧を取得する。'))]
#[Post(openapi: new Operation(summary: 'コメントを新規作成する。'))]
#[Get(
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
)]
#[Delete(
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
)]
#[Patch(
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
)]
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
}
