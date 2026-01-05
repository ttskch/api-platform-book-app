<?php

namespace App\Tests\Functional\Traits;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Repository\UserRepository;

trait ClientTrait
{
    private static function createAuthenticatedClient(string $clerkUserId): Client
    {
        $user = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['clerkUserId' => $clerkUserId]);

        if ($user === null) {
            throw new \LogicException(sprintf('clerkUserId "%s" のユーザーが存在しません。', $clerkUserId));
        }

        return static::createClient()->loginUser($user);
    }
}
