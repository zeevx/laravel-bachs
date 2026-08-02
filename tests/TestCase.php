<?php

declare(strict_types=1);

namespace Zeevx\LaravelBachs\Tests;

use Zeevx\LaravelBachs\BachsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [BachsServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('bachs.api_key', 'test_key');
        $app['config']->set('bachs.webhook.secret', 'webhook_secret');
    }
}
