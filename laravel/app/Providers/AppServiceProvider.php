<?php

namespace App\Providers;

use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\State\Processor\WriteProcessor;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\ApiPlatform\Hydra\JsonSchema\SchemaFactory;
use App\ApiPlatform\OpenApi\Factory\OpenApiFactory;
use App\State\AppWriteProcessor;
use App\State\ArticleProcessor;
use App\State\ArticlePublishProcessor;
use App\State\CommentCreateProvider;
use App\State\MediaObjectPostProcessor;
use App\State\TagCollectionProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
