<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiResource(
    rules: [
        'article' => ['required'],
        'content' => ['required'],
    ],
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
