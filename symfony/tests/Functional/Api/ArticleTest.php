<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Article;
use App\Repository\UserRepository;
use App\Tests\Factory\ArticleFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ArticleTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    protected function setUp(): void
    {
        self::$alwaysBootKernel = false;

        Clock::set(new MockClock('2026-01-01 00:00:00'));

        UserFactory::createSequence([
            ['clerkUserId' => 'user1'],
            ['clerkUserId' => 'user2'],
        ]);
    }

    public function testGet(): void
    {
        ArticleFactory::createSequence(function () {
            foreach (range(1, 2) as $i) {
                yield [
                    'title' => sprintf('title%d', $i),
                    'published' => $i === 1,
                    'date' => new \DateTimeImmutable('2026-01-01'),
                    'createdBy' => sprintf('user%d', $i),
                ];
            }
        });

        // 未ログイン状態では未公開記事は閲覧不可
        $iri = $this->findIriBy(Article::class, ['published' => false]);
        self::createClient()->request('GET', $iri);
        self::assertResponseStatusCodeSame(404);

        // 未ログイン状態でも公開済み記事は閲覧可能
        $iri = $this->findIriBy(Article::class, ['published' => true]);
        $response = self::createClient()->request('GET', $iri);
        self::assertResponseIsSuccessful();

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(Article::class);

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

        self::assertJsonEquals([
            '@context' => '/api/contexts/Article',
            '@id' => '/api/articles/1',
            '@type' => 'Article',
            'id' => 1,
            'title' => 'title1',
            'published' => true,
            'comments' => [],
            'tags' => [],
            'relatedArticles' => [],
            'date' => '2026-01-01',
            'createdBy' => 'user1',
            'createdAt' => '2026-01-01T00:00:00+09:00',
            'updatedAt' => '2026-01-01T00:00:00+09:00',
            'popular' => false,
        ]);

        // ログイン状態でも他人の未公開記事は閲覧不可
        $iri = $this->findIriBy(Article::class, [
            'createdBy' => 'user2',
            'published' => false,
        ]);
        self::createAuthenticatedClient('user1')->request('GET', $iri);
        self::assertResponseStatusCodeSame(404);

        // 自分のものなら未公開記事も閲覧可能
        $iri = $this->findIriBy(Article::class, [
            'createdBy' => 'user2',
            'published' => false,
        ]);
        self::createAuthenticatedClient('user2')->request('GET', $iri);
        self::assertResponseIsSuccessful();
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
