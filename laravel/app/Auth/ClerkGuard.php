<?php

namespace App\Auth;

use App\Models\User;
use Clerk\Backend\Helpers\Jwks\AuthenticateRequest;
use Clerk\Backend\Helpers\Jwks\AuthenticateRequestOptions;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class ClerkGuard implements Guard
{
    use GuardHelpers;

    public function __construct(
        UserProvider $provider,
        private Request $request,
    ) {
        $this->provider = $provider;
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $options = new AuthenticateRequestOptions(
            secretKey: config('services.clerk.secret'),
            authorizedParties: json_decode(config('services.clerk.authorized_parties', '[]'), true, flags: JSON_THROW_ON_ERROR),
        );
        $requestState = AuthenticateRequest::authenticateRequest(
            $this->request,
            $options,
        );
        $clerkUserId = strval($requestState->getPayload()?->sub) ?: null;

        if ($clerkUserId === null) {
            return null;
        }

        $user = User::firstOrCreate(['clerk_user_id' => $clerkUserId]);

        return $this->user = $user;
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }
}
