<?php

namespace App\Providers;

use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Serializer\JsonEncoder;
use ApiPlatform\State\Processor\WriteProcessor;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\ApiPlatform\Hydra\JsonSchema\SchemaFactory;
use App\ApiPlatform\OpenApi\Factory\OpenApiFactory;
use App\Serializer\MediaObjectDenormalizer;
use App\Serializer\MultipartDecoder;
use App\Serializer\UploadedFileDenormalizer;
use App\State\AppWriteProcessor;
use App\State\ArticleProcessor;
use App\State\ArticlePublishProcessor;
use App\State\CommentCreateProvider;
use App\State\MediaObjectPostProcessor;
use App\State\TagCollectionProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * {@see ApiPlatformProvider::register()} における Serializer::class サービスの定義を上書き
         */
        $this->app->singleton(Serializer::class, function (Application $app) {
            return new Serializer(
                [
                    new MediaObjectDenormalizer( // 競合するデノーマライザーより優先的に使われるよう先頭に追加
                        $normalizers = iterator_to_array($app->make('api_platform_normalizer_list')),
                    ),
                    new UploadedFileDenormalizer(), // 追加（競合するデノーマライザーがないため現状は実質不要ではあるが）
                    ...$normalizers,
                ],
                [
                    new JsonEncoder('json'),
                    $app->make(JsonEncoder::class),
                    new JsonEncoder('jsonopenapi'),
                    new JsonEncoder('jsonapi'),
                    new JsonEncoder('jsonhal'),
                    new CsvEncoder(),
                    new MultipartDecoder(), // 追加
                ],
            );
        });

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->tag([
            TagCollectionProvider::class,
            CommentCreateProvider::class,
        ], ProviderInterface::class);

        $this->app->tag([
            ArticleProcessor::class,
            ArticlePublishProcessor::class,
            MediaObjectPostProcessor::class,
        ], ProcessorInterface::class);

        $this->app->extend(OpenApiFactoryInterface::class, function (OpenApiFactoryInterface $inner) {
            return new OpenApiFactory($inner);
        });

        $this->app->extend(SchemaFactoryInterface::class, function (SchemaFactoryInterface $inner) {
            return new SchemaFactory($inner);
        });

        $this->app->extend(WriteProcessor::class, function (WriteProcessor $inner, Application $app) {
            return new AppWriteProcessor($inner, $app->make(LoggerInterface::class));
        });
    }
}
