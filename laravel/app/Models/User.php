<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\Auth\ClerkGuard;
use App\State\UserMeProvider;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiProperty(
    property: 'id',
    serialize: new Groups(['user:read:item']),
)]
#[ApiProperty(
    property: 'clerkUserId',
    serialize: new Groups(['user:read:item']),
)]
class User extends Authenticatable
{
    public $timestamps = false;

    /**
     * {@see ClerkGuard::user()} で `User::firstOrCreate()` を使用しているので $fillable の定義が必要
     */
    protected $fillable = [
        'clerk_user_id',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public static function apiResource(): array
    {
        return [
            new ApiResource(
                normalizationContext: ['groups' => ['user:read:item']],
            ),
            new Get(
                uriTemplate: '/users/me',
                openapi: new Operation(
                    summary: 'ログイン中のユーザー自身の詳細を取得する。',
                ),
                provider: UserMeProvider::class,
            ),
        ];
    }
}
