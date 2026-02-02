<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Article;
use App\Tests\Factory\ArticleFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Functional\Traits\ClientTrait;
use App\Tests\Functional\Traits\ResetSequenceTrait;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ArticleTest extends ApiTestCase
{
    use ClientTrait;
    use Factories;
    use MatchesSnapshots;
    use ResetDatabase;
    use ResetSequenceTrait;

    protected function setUp(): void
    {
        self::$alwaysBootKernel = false;

        Clock::set(new MockClock('2026-01-01 00:00:00'));

        UserFactory::createSequence([
            ['clerkUserId' => 'user1'],
            ['clerkUserId' => 'user2'],
        ]);
    }

    public function testGetCollection(): void
    {
        ArticleFactory::createSequence(function () {
            foreach (range(1, 50) as $i) {
                yield [
                    'title' => sprintf('title%d', $i),
                    'date' => new \DateTimeImmutable('2026-01-01'),
                    'published' => true,
                ];
            }
        });
        ArticleFactory::createSequence(function () {
            foreach (range(51, 100) as $i) {
                yield [
                    'title' => sprintf('title%d', $i),
                    'date' => new \DateTimeImmutable('2026-01-01'),
                    'published' => false,
                    'createdBy' => 'user1',
                ];
            }
        });

        $response = self::createClient()->request('GET', '/api/articles');
        self::assertResponseIsSuccessful();

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceCollectionJsonSchema(Article::class);

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

        // 未ログイン状態では公開済み記事のみ閲覧可能
        self::assertSame(50, $response->toArray()['totalItems']);

        // 正しくページネートされている
        self::assertCount(30, $response->toArray()['member']);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());

        // ログイン状態でも他人の未公開記事は閲覧不可
        $response = self::createAuthenticatedClient('user2')
            ->request('GET', '/api/articles');
        self::assertSame(50, $response->toArray()['totalItems']);

        // 自分のものなら未公開記事も閲覧可能
        $response = self::createAuthenticatedClient('user1')
            ->request('GET', '/api/articles');
        self::assertSame(100, $response->toArray()['totalItems']);

        // フィルターが機能する
        $response = self::createAuthenticatedClient('user1')
            ->request('GET', '/api/articles?title=title100');
        self::assertSame(1, $response->toArray()['totalItems']);
        $response = self::createAuthenticatedClient('user1')
            ->request('GET', '/api/articles?query=title100');
        self::assertSame(1, $response->toArray()['totalItems']);

        // 並べ替えが機能する
        $response = self::createAuthenticatedClient('user1')
            ->request('GET', '/api/articles?order[id]=desc');
        $ids = array_column($response->toArray()['member'], 'id');
        $orderedIds = $ids;
        rsort($orderedIds);
        self::assertSame($orderedIds, $ids);
        $response = self::createAuthenticatedClient('user1')
            ->request('GET', '/api/articles?order[date]=asc');
        $dates = array_column($response->toArray()['member'], 'date');
        $orderedDates = $dates;
        sort($orderedDates);
        self::assertSame($orderedDates, $dates);
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

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());

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

    public function testPost(): void
    {
        // 未ログイン状態ではアクセス不可
        self::createClient()->request('POST', '/api/articles', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        self::assertResponseStatusCodeSame(401);

        // バリデーションエラー
        $response = self::createAuthenticatedClient('user1')
            ->request('POST', '/api/articles', [
                'json' => [
                    'title' => str_pad('*', 256),
                    'tags' => ['invalid-tag'],
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ]);
        self::assertResponseStatusCodeSame(422);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray(false));

        // 正常系
        $response = self::createAuthenticatedClient('user1')
            ->request('POST', '/api/articles', [
                'json' => [
                    'title' => 'title1',
                    'date' => '2026-01-01',
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ]);
        self::assertResponseStatusCodeSame(201);

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(Article::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());
    }

    public function testPutPublication(): void
    {
        ArticleFactory::createSequence(function () {
            foreach (range(1, 2) as $i) {
                yield [
                    'title' => sprintf('title%d', $i),
                    'published' => true,
                    'date' => new \DateTimeImmutable('2026-01-01'),
                    'createdBy' => sprintf('user%d', $i),
                ];
            }
        });

        // 未ログイン状態ではアクセス不可
        $iri = $this->findIriBy(Article::class, ['title' => 'title1']);
        self::createClient()->request('PUT', sprintf('%s/publication', $iri));
        self::assertResponseStatusCodeSame(401);

        // 他人の記事は更新不可
        $iri = $this->findIriBy(Article::class, ['createdBy' => 'user2']);
        self::createAuthenticatedClient('user1')
            ->request('PUT', sprintf('%s/publication', $iri));
        self::assertResponseStatusCodeSame(403);

        // 自分の記事は更新可能
        $iri = $this->findIriBy(Article::class, ['createdBy' => 'user1']);
        $response = self::createAuthenticatedClient('user1')
            ->request('PUT', sprintf('%s/publication', $iri));
        self::assertResponseStatusCodeSame(200);

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(Article::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());
    }

    public function testPatch(): void
    {
        ArticleFactory::createSequence(function () {
            foreach (range(1, 2) as $i) {
                yield [
                    'title' => sprintf('title%d', $i),
                    'published' => true,
                    'date' => new \DateTimeImmutable('2026-01-01'),
                    'createdBy' => sprintf('user%d', $i),
                ];
            }
        });

        // 未ログイン状態ではアクセス不可
        $iri = $this->findIriBy(Article::class, ['title' => 'title1']);
        self::createClient()->request('PATCH', $iri);
        self::assertResponseStatusCodeSame(401);

        // 他人の記事は編集不可
        $iri = $this->findIriBy(Article::class, ['createdBy' => 'user2']);
        self::createAuthenticatedClient('user1')->request('PATCH', $iri);
        self::assertResponseStatusCodeSame(403);

        // バリデーションエラー
        $iri = $this->findIriBy(Article::class, ['createdBy' => 'user1']);
        $response = self::createAuthenticatedClient('user1')
            ->request('PATCH', $iri, [
                'json' => [
                    'title' => str_pad('*', 256),
                    'tags' => ['invalid-tag'],
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ]);
        self::assertResponseStatusCodeSame(422);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray(false));

        Clock::set(new MockClock('2026-01-02 00:00:00'));

        // 正常系
        $iri = $this->findIriBy(Article::class, ['createdBy' => 'user1']);
        $response = self::createAuthenticatedClient('user1')
            ->request('PATCH', $iri, [
                'json' => [
                    'title' => 'new title',
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ]);
        self::assertResponseStatusCodeSame(200);

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(Article::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());
    }

    public function testDelete(): void
    {
        ArticleFactory::createSequence(function () {
            foreach (range(1, 2) as $i) {
                yield [
                    'title' => sprintf('title%d', $i),
                    'published' => true,
                    'date' => new \DateTimeImmutable('2026-01-01'),
                    'createdBy' => sprintf('user%d', $i),
                ];
            }
        });

        // 未ログイン状態ではアクセス不可
        $iri = $this->findIriBy(Article::class, ['title' => 'title1']);
        self::createClient()->request('DELETE', $iri);
        self::assertResponseStatusCodeSame(401);

        // 他人の記事は削除不可
        $iri = $this->findIriBy(Article::class, ['createdBy' => 'user2']);
        self::createAuthenticatedClient('user1')->request('DELETE', $iri);
        self::assertResponseStatusCodeSame(403);
        self::assertSame(2, ArticleFactory::count());

        // 正常系
        $iri = $this->findIriBy(Article::class, ['createdBy' => 'user1']);
        self::createAuthenticatedClient('user1')->request('DELETE', $iri);
        self::assertResponseStatusCodeSame(204);
        self::assertSame(1, ArticleFactory::count());
    }
}
