<?php

namespace App\Providers;

use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\ApiPlatform\Hydra\JsonSchema\SchemaFactory;
use App\ApiPlatform\OpenApi\Factory\OpenApiFactory;
use App\State\ArticlePostProcessor;
use App\State\ArticleProcessor;
use App\State\ArticlePublishProcessor;
use App\State\TagCollectionProvider;
use Illuminate\Support\ServiceProvider;

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
        $this->app->tag(TagCollectionProvider::class, ProviderInterface::class);

        $this->app->tag([
            ArticleProcessor::class,
            ArticlePostProcessor::class,
            ArticlePublishProcessor::class,
        ], ProcessorInterface::class);

        $this->app->extend(OpenApiFactoryInterface::class, function (OpenApiFactoryInterface $inner) {
            return new OpenApiFactory($inner);
        });

        $this->app->extend(SchemaFactoryInterface::class, function (SchemaFactoryInterface $inner) {
            return new SchemaFactory($inner);
        });
    }
}
