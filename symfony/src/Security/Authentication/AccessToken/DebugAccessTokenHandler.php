<?php

namespace App\Security\Authentication\AccessToken;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class DebugAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private ClerkSessionTokenHandler $decorated,
        #[Autowire(env: 'DEBUG_CLERK_USER_ID')]
        private ?string $clerkUserId = null,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        return $this->clerkUserId !== null
            ? new UserBadge($this->clerkUserId)
            : $this->decorated->getUserBadgeFrom($accessToken)
        ;
    }
}
