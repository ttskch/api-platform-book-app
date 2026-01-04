<?php

namespace App\Tests\Unit\State;

use ApiPlatform\Metadata\Get;
use App\Entity\User;
use App\State\UserMeProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class UserMeProviderTest extends TestCase
{
    public function testProvide(): void
    {
        $security = self::createStub(Security::class);
        $security->method('getUser')->willReturn($expected = new User());

        $SUT = new UserMeProvider($security);

        $actual = $SUT->provide(new Get());

        self::assertSame($expected, $actual);
    }
}
