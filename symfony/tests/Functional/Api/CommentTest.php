<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Doctrine\ArticleExtension;
use App\Entity\Article;
use App\Entity\Comment;
use App\Tests\Factory\ArticleFactory;
use App\Tests\Factory\CommentFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Functional\Traits\ClientTrait;
use App\Tests\Functional\Traits\ResetSequenceTrait;
use Spatie\Snapshots\MatchesSnapshots;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CommentTest extends ApiTestCase
{
    use ClientTrait;
    use Factories;
    use MatchesSnapshots;
    use ResetDatabase;
    use ResetSequenceTrait;

    protected function setUp(): void
    {
        self::$alwaysBootKernel = false;

        UserFactory::createSequence([
            ['clerkUserId' => 'user1'],
            ['clerkUserId' => 'user2'],
        ]);

        ArticleFactory::createOne([
            'title' => 'title1',
            /**
             * {@see ArticleExtension} により公開済みまたは自分が作成した記事の配下にしかコメントを新規作成できないため、
             * {@see self::testPost()} が期待どおりに動作するためには、親となるこの記事が必ず公開済みである必要がある
             */
            'published' => true,
        ]);

        CommentFactory::createSequence(function () {
            foreach (range(1, 2) as $i) {
                yield [
                    'article' => ArticleFactory::find(['title' => 'title1']),
                    'content' => sprintf('content%d', $i),
                    'createdBy' => sprintf('user%d', $i),
                ];
            }
        });
    }

    public function testGetCollection(): void
    {
        CommentFactory::createSequence(function () {
            foreach (range(3, 100) as $i) {
                yield [
                    'article' => ArticleFactory::find(['title' => 'title1']),
                    'content' => sprintf('content%d', $i),
                ];
            }
        });

        // 未ログイン状態でも全件閲覧可能
        $articleIri = $this->findIriBy(Article::class, ['title' => 'title1']);
        $response = self::createClient()
            ->request('GET', sprintf('%s/comments', $articleIri));
        self::assertSame(100, $response->toArray()['totalItems']);

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceCollectionJsonSchema(Comment::class);

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

        // 正しくページネートされている
        self::assertCount(30, $response->toArray()['member']);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());
    }

    public function testGet(): void
    {
        // 未ログイン状態でも閲覧可能
        $iri = $this->findIriBy(Comment::class, ['content' => 'content1']);
        $response = self::createClient()->request('GET', $iri);
        self::assertResponseIsSuccessful();

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(Comment::class);

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

    public function testPost(): void
    {
        // 未ログイン状態ではアクセス不可
        $articleIri = $this->findIriBy(Article::class, ['title' => 'title1']);
        self::createClient()
            ->request('POST', sprintf('%s/comments', $articleIri), [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ]);
        self::assertResponseStatusCodeSame(401);

        // バリデーションエラー
        $articleIri = $this->findIriBy(Article::class, ['title' => 'title1']);
        $response = self::createAuthenticatedClient('user1')->request('POST', sprintf('%s/comments', $articleIri), [
            'json' => [],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        self::assertResponseStatusCodeSame(422);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray(false));

        // 正常系
        $articleIri = $this->findIriBy(Article::class, ['title' => 'title1']);
        $response = self::createAuthenticatedClient('user1')
            ->request('POST', sprintf('%s/comments', $articleIri), [
                'json' => [
                    'content' => 'content3',
                ],
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
            ]);
        self::assertResponseStatusCodeSame(201);

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(Comment::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());
    }

    public function testPatch(): void
    {
        // 未ログイン状態ではアクセス不可
        $iri = $this->findIriBy(Comment::class, ['content' => 'content1']);
        self::createClient()->request('PATCH', $iri);
        self::assertResponseStatusCodeSame(401);

        // 他人のコメントは編集不可
        $iri = $this->findIriBy(Comment::class, ['createdBy' => 'user2']);
        self::createAuthenticatedClient('user1')->request('PATCH', $iri);
        self::assertResponseStatusCodeSame(403);

        // バリデーションエラー
        $iri = $this->findIriBy(Comment::class, ['createdBy' => 'user1']);
        $response = self::createAuthenticatedClient('user1')
            ->request('PATCH', $iri, [
                'json' => [
                    'content' => '',
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ]);
        self::assertResponseStatusCodeSame(422);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray(false));

        // 正常系
        $iri = $this->findIriBy(Comment::class, ['createdBy' => 'user1']);
        $response = self::createAuthenticatedClient('user1')->request('PATCH', $iri, [
            'json' => [
                'content' => 'new content',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);
        self::assertResponseStatusCodeSame(200);

        // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        self::assertMatchesResourceItemJsonSchema(Comment::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->toArray());
    }

    public function testDelete(): void
    {
        // 未ログイン状態ではアクセス不可
        $iri = $this->findIriBy(Comment::class, ['content' => 'content1']);
        self::createClient()->request('DELETE', $iri);
        self::assertResponseStatusCodeSame(401);

        // 他人のコメントは削除不可
        $iri = $this->findIriBy(Comment::class, ['createdBy' => 'user2']);
        self::createAuthenticatedClient('user1')->request('DELETE', $iri);
        self::assertResponseStatusCodeSame(403);
        self::assertSame(2, CommentFactory::count());

        // 正常系
        $iri = $this->findIriBy(Comment::class, ['createdBy' => 'user1']);
        self::createAuthenticatedClient('user1')->request('DELETE', $iri);
        self::assertResponseStatusCodeSame(204);
        self::assertSame(1, CommentFactory::count());
    }
}
