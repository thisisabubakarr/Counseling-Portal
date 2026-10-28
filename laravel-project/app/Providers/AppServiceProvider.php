<?php

namespace App\Providers;

use App\Services\Cms\CmsClientInterface;
use App\Services\Cms\MockCmsClient;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CmsClientInterface::class, function () {
            return match (config('services.cms.client')) {
                'mock' => new MockCmsClient(),
                default => throw new RuntimeException(
                    "Unsupported services.cms.client driver [".config('services.cms.client')."]."
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
