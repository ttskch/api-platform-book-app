<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\MediaObject;
use App\Repository\UserRepository;
use App\Tests\Factory\UserFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class MediaObjectTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

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

        self::createAuthenticatedClient('user1')
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

        self::assertJsonEquals([
            '@context' => '/api/contexts/MediaObject',
            '@id' => '/api/media_objects/1',
            '@type' => 'https://schema.org/MediaObject',
            'contentUrl' => 'http://localhost/fake-uri',
        ]);
    }

    private static function createAuthenticatedClient(string $clerkUserId): Client
    {
        $user = self::getContainer()->get(UserRepository::class)
            ->findOneBy(['clerkUserId' => $clerkUserId]);

        if ($user === null) {
            throw new \LogicException(sprintf('clerkUserId "%s" のユーザーが存在しません。', $clerkUserId));
        }

        return self::createClient()->loginUser($user);
    }
}
