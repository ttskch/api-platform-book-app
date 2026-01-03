<?php

namespace App\Security\Authentication\AccessToken;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

class DebugAccessTokenExtractor implements AccessTokenExtractorInterface
{
    public function __construct(
        #[Autowire(env: 'DEBUG_CLERK_USER_ID')]
        private ?string $clerkUserId = null,
    ) {
    }

    public function extractAccessToken(Request $request): ?string
    {
        return $this->clerkUserId !== null ? 'dummy' : null;
    }
}
