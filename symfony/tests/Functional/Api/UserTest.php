<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Tests\Factory\UserFactory;
use App\Tests\Functional\Traits\ClientTrait;
use App\Tests\Functional\Traits\ResetSequenceTrait;
use Spatie\Snapshots\MatchesSnapshots;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserTest extends ApiTestCase
{
    use ClientTrait;
    use Factories;
    use MatchesSnapshots;
    use ResetDatabase;
    use ResetSequenceTrait;

    protected function setUp(): void
    {
        self::$alwaysBootKernel = false;
    }

    public function testGetMe(): void
    {
        UserFactory::createOne(['clerkUserId' => 'user1']);

        // 未ログイン状態では404
        self::createClient()->request('GET', '/api/users/me');
        self::assertResponseStatusCodeSame(404);

        // 正常系
        $response = self::createAuthenticatedClient('user1')
            ->request('GET', '/api/users/me');
        self::assertResponseIsSuccessful();

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(User::class);

        // レスポンスヘッダーが意図どおりであることを検査
        self::assertResponseHasHeader('etag');
        self::assertResponseHeaderSame(
            'cache-control',
            'max-age=0, stale-if-error=86400, private',
        );
        self::assertSame(
            ['Accept', 'Content-Type', 'Authorization', 'Origin'],
            $response->getHeaders()['vary'] ?? null,
        );

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());
    }
}
