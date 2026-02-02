<?php

namespace Tests\Feature\Api;

use ApiPlatform\Laravel\Test\ApiTestAssertionsTrait;
use App\Models\MediaObject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

class MediaObjectTest extends TestCase
{
    use ApiTestAssertionsTrait;
    use MatchesSnapshots;
    use RefreshDatabase;

    public function testGet(): void
    {
        $mediaObject1 = MediaObject::factory()->createOne(['file_path' => 'file1']);

        $iri = $this->getIriFromResource($mediaObject1);
        $response = $this->getJson(uri: $iri, headers: ['Accept' => 'application/ld+json'])->assertSuccessful();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(MediaObject::class);

        // レスポンスヘッダーが意図どおりであることを検査
        // 以下はLaravel版では未対応
        // self::assertResponseHasHeader('etag');
        // self::assertResponseHeaderSame(
        //     'cache-control',
        //     'max-age=0, public, s-maxage=31536000, stale-if-error=86400',
        // );
        self::assertNotNull($response->headers->get('etag'));
        self::assertSame(
            'max-age=0, public, s-maxage=31536000, stale-if-error=86400',
            $response->headers->get('cache-control'),
        );
        self::assertSame(
            ['Accept', 'Content-Type', 'Authorization', 'Origin'],
            $response->headers->all('vary'),
        );

        // レスポンスヘッダーが意図どおりであることを検査
        // 以下はLaravel版では未対応
        // self::assertResponseHasHeader('etag');
        // self::assertResponseHeaderSame('cache-control', 'max-age=0, stale-if-error=86400, private');

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());
    }

    public function testPost(): void
    {
        $user1 = User::factory()->createOne(['clerk_user_id' => 'user1']);

        Storage::fake('public');

        Str::createUuidsUsing(fn () => Uuid::fromString('00000000-0000-0000-0000-000000000000'));

        /** {@see UploadedFile::isValid()} が {@see \is_uploaded_file()} をチェックするため、new で作ったものではアップロードが失敗する */
        // $file1 = new UploadedFile(__DIR__. '/../resources/image.png', 'image.png', 'image/png');
        // $file2 = new UploadedFile(__DIR__. '/../resources/image.png', 'image.png', 'image/png');
        $file1 = UploadedFile::fake()->image('image.png');
        $file2 = UploadedFile::fake()->image('image.png');

        // 未ログイン状態ではアクセス不可
        $this->post(uri: '/api/media_objects', data: [
            'file' => $file1,
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'multipart/form-data',
        ])->assertForbidden();

        // バリデーションエラー
        $response = $this->actingAs($user1)->post(uri: '/api/media_objects', data: [
            // no data
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'multipart/form-data',
        ])->assertUnprocessable();

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());

        // 正常系
        $response = $this->actingAs($user1)->post(uri: '/api/media_objects', data: [
            'file' => $file2,
        ], headers: [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'multipart/form-data',
        ])->assertCreated();

        // Laravel版では未対応
        // // レスポンスボディが自動生成されたJSON Schemaに適合していることを検査
        // self::assertMatchesResourceItemJsonSchema(MediaObject::class);

        // スナップショットテスト
        self::assertMatchesJsonSnapshot($response->json());

        Str::createUuidsNormally();
    }
}
