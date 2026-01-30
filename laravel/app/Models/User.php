<?php

namespace App\Models;

use App\Auth\ClerkGuard;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
}
