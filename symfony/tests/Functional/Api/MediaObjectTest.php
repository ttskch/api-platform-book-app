<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\MediaObject;
use App\Tests\Factory\UserFactory;
use App\Tests\Functional\Traits\ClientTrait;
use App\Tests\Functional\Traits\ResetSequenceTrait;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class MediaObjectTest extends ApiTestCase
{
    use ClientTrait;
    use Factories;
    use MatchesSnapshots;
    use ResetDatabase;
    use ResetSequenceTrait;

    protected function setUp(): void
    {
        self::$alwaysBootKernel = false;

        UserFactory::createOne(['clerkUserId' => 'user1']);
    }

    public function testPost(): void
    {
        // 未ログイン状態ではアクセス不可
        self::createClient()->request('POST', '/api/media_objects', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
        ]);
        self::assertResponseStatusCodeSame(401);

        $file = new UploadedFile(
            __DIR__.'/../../resources/image.png',
            'image.png',
        );

        $response = self::createAuthenticatedClient('user1')
            ->request('POST', '/api/media_objects', [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'extra' => [
                    'files' => [
                        'file' => $file,
                    ],
                ],
            ]);
        self::assertResponseStatusCodeSame(201);

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(MediaObject::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());
    }
}
