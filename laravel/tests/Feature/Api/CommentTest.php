<?php

namespace Tests\Feature\Api;

use ApiPlatform\Laravel\Test\ApiTestAssertionsTrait;
use App\Eloquent\ArticleExtension;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use ApiTestAssertionsTrait;
    use MatchesSnapshots;
    use RefreshDatabase;

    private User $user1;
    private Article $article1;
    private Comment $comment1;
    private Comment $comment2;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->user1, $this->user2] = User::factory()->count(2)->sequence(
            ['id' => 1, 'clerk_user_id' => 'user1'],
            ['id' => 2, 'clerk_user_id' => 'user2'],
        )->create();

        $this->article1 = Article::factory()->createOne([
            'title' => 'title1',
            /**
             * {@see ArticleExtension} により公開済みまたは自分が作成した記事の配下にしかコメントを新規作成できないため、
             * {@see self::testPost()} が期待どおりに動作するためには、親となるこの記事が必ず公開済みである必要がある
             */
            'published' => true,
        ]);

        // created_by プロパティに固定値をセットするにはモデルイベントの無効化が必要
        [$this->comment1, $this->comment2] = Comment::withoutEvents(fn () => Comment::factory()->count(2)->state(new Sequence(fn (Sequence $sequence) => [
            'content' => sprintf('content%d', $sequence->index + 1),
            'created_by' => $sequence->index + 1,
        ]))->for($this->article1)->create());
    }

    public function testGetCollection(): void
    {
        Comment::factory()->count(98)->state(new Sequence(fn (Sequence $sequence) => [
            'content' => sprintf('content%d', $sequence->index + 3),
        ]))->for($this->article1)->create();

        // 未ログイン状態でも全件閲覧可能
        $articleIri = $this->getIriFromResource($this->article1);
        $response = $this->getJson(
            uri: sprintf('%s/comments', $articleIri),
            headers: ['Accept' => 'application/ld+json'],
        )->assertSuccessful();
        self::assertSame(100, $response->json('totalItems'));

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceCollectionJsonSchema(Comment::class);

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

        // 正しくページネートされている
        self::assertCount(30, $response->json('member'));

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());
    }

    public function testGet(): void
    {
        // 未ログイン状態でも閲覧可能
        $iri = $this->getIriFromResource($this->comment1);
        $response = $this->getJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertSuccessful();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(Comment::class);

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

    public function testPost(): void
    {
        // 未ログイン状態ではアクセス不可
        $articleIri = $this->getIriFromResource($this->article1);
        $this->postJson(uri: sprintf('%s/comments', $articleIri), data: [
            'content' => 'content3',
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/ld+json',
        ])->assertForbidden();

        // バリデーションエラー
        $articleIri = $this->getIriFromResource($this->article1);
        $response = $this->actingAs($this->user1)->postJson(uri: sprintf('%s/comments', $articleIri), data: [
            // no data
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/ld+json',
        ])->assertUnprocessable();

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());

        // 正常系
        $articleIri = $this->getIriFromResource($this->article1);
        $response = $this->actingAs($this->user1)->postJson(uri: sprintf('%s/comments', $articleIri), data: [
            'content' => 'content3',
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/ld+json',
        ])->assertCreated();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(Comment::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());
    }

    public function testPatch(): void
    {
        // 未ログイン状態ではアクセス不可
        $iri = $this->getIriFromResource($this->comment1);
        $this->patchJson(uri: $iri, headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/merge-patch+json',
        ])->assertForbidden();

        // 他人のコメントは編集不可
        $iri = $this->getIriFromResource($this->comment2); // created_by: 2
        $this->actingAs($this->user1)->patchJson(uri: $iri, headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/merge-patch+json',
        ])->assertForbidden();

        // 正常系
        $iri = $this->getIriFromResource($this->comment1); // created_by: 1
        $response = $this->actingAs($this->user1)->patchJson(uri: $iri, data: [
            'content' => 'new content',
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/merge-patch+json',
        ])->assertSuccessful();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(Comment::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());
    }

    public function testDelete(): void
    {
        // 未ログイン状態ではアクセス不可
        $iri = $this->getIriFromResource($this->comment1);
        $this->deleteJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertForbidden();

        // 他人のコメントは削除不可
        $iri = $this->getIriFromResource($this->comment2); // created_by: 2
        $this->actingAs($this->user1)->deleteJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertForbidden();
        self::assertSame(2, Comment::count());

        // 正常系
        $iri = $this->getIriFromResource($this->comment1); // created_by: 1
        $this->actingAs($this->user1)->deleteJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertNoContent();
        self::assertSame(1, Comment::count());
    }
}
