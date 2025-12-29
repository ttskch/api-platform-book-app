<?php

namespace App\Providers;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\State\ArticlePostProcessor;
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

        $this->app->tag(ArticlePostProcessor::class, ProcessorInterface::class);
    }
}
