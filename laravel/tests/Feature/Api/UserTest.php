<?php

namespace Tests\Feature\Api;

use ApiPlatform\Laravel\Test\ApiTestAssertionsTrait;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

class UserTest extends TestCase
{
    use ApiTestAssertionsTrait;
    use MatchesSnapshots;
    use RefreshDatabase;

    public function testGetMe(): void
    {
        $user1 = User::factory()->createOne(['clerk_user_id' => 'user1']);

        // 未ログイン状態では404
        $this->getJson('/api/users/me')->assertNotAcceptable();

        // 正常系
        $response = $this->actingAs($user1)->getJson(
            uri: '/api/users/me',
            headers: ['Accept' => 'application/ld+json'],
        )->assertSuccessful();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(User::class);

        // レスポンスヘッダーが意図どおりであることを検査
        // 以下はLaravel版では未対応
        // self::assertResponseHasHeader('etag');
        // self::assertResponseHeaderSame(
        //     'cache-control',
        //     'max-age=0, stale-if-error=86400, private',
        // );
        self::assertNotNull($response->headers->get('etag'));
        self::assertSame(
            'max-age=0, stale-if-error=86400, private',
            $response->headers->get('cache-control'),
        );
        self::assertSame(
            ['Accept', 'Content-Type', 'Authorization', 'Origin'],
            $response->headers->all('vary'),
        );

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());
    }
}
