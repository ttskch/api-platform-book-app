<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;

class DebugGuard implements Guard
{
    use GuardHelpers;

    public function __construct(
        private ClerkGuard $decorated,
    ) {
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if (($clerkUserId = config('services.clerk.debug_user_id')) !== null) {
            $user = User::firstOrCreate(['clerk_user_id' => $clerkUserId]);

            return $this->user = $user;
        }

        return $this->decorated->user();
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }
}
