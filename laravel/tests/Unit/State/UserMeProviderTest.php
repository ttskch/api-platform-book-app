<?php

namespace Tests\Unit\State;

use ApiPlatform\Metadata\Get;
use App\Models\User;
use App\State\UserMeProvider;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\TestCase;

class UserMeProviderTest extends TestCase
{
    public function testProvide(): void
    {
        Auth::expects('user')->once()->withNoArgs()->andReturn($expected = new User());

        $SUT = new UserMeProvider();

        $actual = $SUT->provide(new Get());

        self::assertSame($expected, $actual);
    }
}
