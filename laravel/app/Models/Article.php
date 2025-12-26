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
use App\State\ArticlePostProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    rules: [
        'title' => ['required', 'max:255'],
        'published' => ['required'],
        'tags' => ['array', 'nullable'],
        'tags.*' => ['in:tag1,tag2,tag3,tag4,tag5,tag6,tag7,tag8,tag9,tag10'],
    ],
)]
#[GetCollection(openapi: new Operation(summary: 'ブログ記事の一覧を取得する。'))]
#[Post(
    openapi: new Operation(summary: 'ブログ記事を新規作成する。'),
    processor: ArticlePostProcessor::class,
)]
#[Get(
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
)]
#[Delete(
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
)]
#[Patch(
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

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
