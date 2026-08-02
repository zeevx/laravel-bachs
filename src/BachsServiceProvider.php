<?php

declare(strict_types=1);

namespace Zeevx\LaravelBachs;

use Zeevx\Bachs\Bachs;
use Zeevx\Bachs\Config;
use Illuminate\Support\ServiceProvider;
use Zeevx\Bachs\Webhooks\WebhookVerifier;

final class BachsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bachs.php', 'bachs');

        $this->app->singleton(Bachs::class, fn (): Bachs => new Bachs(new Config(
            apiKey: (string) config('bachs.api_key'),
            baseUrl: (string) config('bachs.base_url'),
            timeout: (int) config('bachs.timeout'),
        )));

        $this->app->singleton(WebhookVerifier::class, fn (): WebhookVerifier => new WebhookVerifier(
            secret: (string) config('bachs.webhook.secret'),
            tolerance: (int) config('bachs.webhook.tolerance'),
        ));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/bachs.php' => config_path('bachs.php'),
        ], 'bachs-config');

        if ((bool) config('bachs.webhook.route_enabled')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }
}
