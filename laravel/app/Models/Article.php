<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
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
