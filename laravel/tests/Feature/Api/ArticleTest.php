<?php

namespace Tests\Feature\Api;

use ApiPlatform\Laravel\Test\ApiTestAssertionsTrait;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use ApiTestAssertionsTrait;
    use MatchesSnapshots;
    use RefreshDatabase;

    private User $user1;
    private User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-01-01 00:00:00');

        [$this->user1, $this->user2] = User::factory()->count(2)->sequence(
            ['id' => 1, 'clerk_user_id' => 'user1'],
            ['id' => 2, 'clerk_user_id' => 'user2'],
        )->create();
    }

    public function testGetCollection(): void
    {
        Article::factory()->count(50)->state(new Sequence(fn (Sequence $sequence) => [
            'title' => sprintf('title%d', $sequence->index + 1),
            'published' => true,
            'date' => new \DateTimeImmutable('2026-01-01'),
        ]))->create();
        // created_by プロパティに固定値をセットするにはモデルイベントの無効化が必要
        Article::withoutEvents(fn () => Article::factory()->count(50)->state(new Sequence(fn (Sequence $sequence) => [
            'title' => sprintf('title%d', $sequence->index + 51),
            'published' => false,
            'date' => new \DateTimeImmutable('2026-01-01'),
            'created_by' => 1,
        ]))->create());

        $response = $this->getJson(
            uri: '/api/articles',
            headers: ['Accept' => 'application/ld+json'],
        )->assertSuccessful();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceCollectionJsonSchema(Article::class);

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

        // 未ログイン状態では公開済み記事のみ閲覧可能
        self::assertSame(50, $response->json('totalItems'));

        // 正しくページネートされている
        self::assertCount(30, $response->json('member'));

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());

        // ログイン状態でも他人の未公開記事は閲覧不可
        $response = $this->actingAs($this->user2)->getJson(
            uri: '/api/articles',
            headers: ['Accept' => 'application/ld+json'],
        );
        self::assertSame(50, $response->json('totalItems'));

        // 自分のものなら未公開記事も閲覧可能
        $response = $this->actingAs($this->user1)->getJson(
            uri: '/api/articles',
            headers: ['Accept' => 'application/ld+json'],
        );
        self::assertSame(100, $response->json('totalItems'));

        // フィルターが機能する
        $response = $this->actingAs($this->user1)->getJson(
            uri: '/api/articles?title=title100',
            headers: ['Accept' => 'application/ld+json'],
        );
        self::assertSame(1, $response->json('totalItems'));
        $response = $this->actingAs($this->user1)->getJson(
            uri: '/api/articles?query=title100',
            headers: ['Accept' => 'application/ld+json'],
        );
        self::assertSame(1, $response->json('totalItems'));

        // 並べ替えが機能する
        $response = $this->actingAs($this->user1)->getJson(
            uri: '/api/articles?order[id]=desc',
            headers: ['Accept' => 'application/ld+json'],
        );
        $ids = array_column($response->json('member'), 'id');
        $orderedIds = $ids;
        rsort($orderedIds);
        self::assertSame($orderedIds, $ids);
        $response = $this->actingAs($this->user1)->getJson(
            uri: '/api/articles?order[date]=asc',
            headers: ['Accept' => 'application/ld+json'],
        );
        $dates = array_column($response->json('member'), 'date');
        $orderedDates = $dates;
        sort($orderedDates);
        self::assertSame($orderedDates, $dates);
    }

    public function testGet(): void
    {
        // created_by プロパティに固定値をセットするにはモデルイベントの無効化が必要
        [$article1, $article2] = Article::withoutEvents(fn () => Article::factory()->count(2)->state(new Sequence(fn (Sequence $sequence) => [
            'title' => sprintf('title%d', $sequence->index + 1),
            'published' => $sequence->index === 0,
            'date' => new \DateTimeImmutable('2026-01-01'),
            'created_by' => $sequence->index + 1,
        ]))->create());

        // 未ログイン状態では未公開記事は閲覧不可
        $iri = $this->getIriFromResource($article2); // published: false
        $this->getJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertNotFound();

        // 未ログイン状態でも公開済み記事は閲覧可能
        $iri = $this->getIriFromResource($article1); // published: true
        $response = $this->getJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertSuccessful();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(Article::class);

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

        // ログイン状態でも他人の未公開記事は閲覧不可
        $iri = $this->getIriFromResource($article2); // created_by: 2, published: false
        $this->actingAs($this->user1)->getJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertNotFound();

        // 自分のものなら未公開記事も閲覧可能
        $iri = $this->getIriFromResource($article2); // created_by: 2, published: false
        $this->actingAs($this->user2)->getJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertSuccessful();
    }

    public function testPost(): void
    {
        // 未ログイン状態ではアクセス不可
        $this->postJson(uri: '/api/articles', data: [
            'title' => 'title1',
            'date' => '2026-01-01',
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/ld+json',
        ])->assertForbidden();

        // バリデーションエラー
        $response = $this->actingAs($this->user1)->postJson(uri: '/api/articles', data: [
            'title' => str_pad('*', 256),
            'tags' => ['invalid-tag'],
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/ld+json',
        ])->assertUnprocessable();

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());

        // 正常系
        $response = $this->actingAs($this->user1)->postJson(uri: '/api/articles', data: [
            'title' => 'title1',
            'date' => '2026-01-01',
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/ld+json',
        ])->assertCreated();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(Article::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());
    }

    public function testPutPublication(): void
    {
        // created_by プロパティに固定値をセットするにはモデルイベントの無効化が必要
        [$article1, $article2] = Article::withoutEvents(fn () => Article::factory()->count(2)->state(new Sequence(fn (Sequence $sequence) => [
            'title' => sprintf('title%d', $sequence->index + 1),
            'published' => true,
            'date' => new \DateTimeImmutable('2026-01-01'),
            'created_by' => $sequence->index + 1,
        ]))->create());

        // 未ログイン状態ではアクセス不可
        $iri = $this->getIriFromResource($article1);
        $this->putJson(uri: sprintf('%s/publication', $iri), headers: ['Accept' => 'application/ld+json'])->assertForbidden();

        // 他人の記事は更新不可
        $iri = $this->getIriFromResource($article2); // created_by: 2
        $this->actingAs($this->user1)->putJson(uri: sprintf('%s/publication', $iri), headers: ['Accept' => 'application/ld+json'])->assertForbidden();

        // 自分の記事は更新可能
        $iri = $this->getIriFromResource($article1); // created_by: 1
        $response = $this->actingAs($this->user1)->putJson(uri: sprintf('%s/publication', $iri), headers: ['Accept' => 'application/ld+json'])->assertSuccessful();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(Article::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());
    }

    public function testPatch(): void
    {
        // created_by プロパティに固定値をセットするにはモデルイベントの無効化が必要
        [$article1, $article2] = Article::withoutEvents(fn () => Article::factory()->count(2)->state(new Sequence(fn (Sequence $sequence) => [
            'title' => sprintf('title%d', $sequence->index + 1),
            'published' => true,
            'date' => new \DateTimeImmutable('2026-01-01'),
            'created_by' => $sequence->index + 1,
        ]))->create());

        // 未ログイン状態ではアクセス不可
        $iri = $this->getIriFromResource($article1);
        $this->patchJson(uri: $iri, headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/merge-patch+json',
        ])->assertForbidden();

        // 他人の記事は編集不可
        $iri = $this->getIriFromResource($article2); // created_by: 2
        $this->actingAs($this->user1)->patchJson(uri: $iri, headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/merge-patch+json',
        ])->assertForbidden();

        // バリデーションエラー
        $iri = $this->getIriFromResource($article1); // created_by: 1
        $response = $this->actingAs($this->user1)->patchJson(uri: $iri, data: [
            'title' => str_pad('*', 256),
            'tags' => ['invalid-tag'],
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/merge-patch+json',
        ])->assertUnprocessable();

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());

        $this->travelTo('2026-01-02 00:00:00');

        // 正常系
        $iri = $this->getIriFromResource($article1); // created_by: 1
        $response = $this->actingAs($this->user1)->patchJson(uri: $iri, data: [
            'title' => 'new title',
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/merge-patch+json',
        ])->assertSuccessful();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(Article::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());
    }

    public function testDelete(): void
    {
        // created_by プロパティに固定値をセットするにはモデルイベントの無効化が必要
        [$article1, $article2] = Article::withoutEvents(fn () => Article::factory()->count(2)->state(new Sequence(fn (Sequence $sequence) => [
            'title' => sprintf('title%d', $sequence->index + 1),
            'published' => true,
            'date' => new \DateTimeImmutable('2026-01-01'),
            'created_by' => $sequence->index + 1,
        ]))->create());

        // 未ログイン状態ではアクセス不可
        $iri = $this->getIriFromResource($article1);
        $this->deleteJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertForbidden();

        // 他人の記事は削除不可
        $iri = $this->getIriFromResource($article2); // created_by: 2
        $this->actingAs($this->user1)->deleteJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertForbidden();
        self::assertSame(2, Article::count());

        // 正常系
        $iri = $this->getIriFromResource($article1); // created_by: 1
        $this->actingAs($this->user1)->deleteJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertNoContent();
        self::assertSame(1, Article::count());
    }
}
