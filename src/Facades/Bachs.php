<?php

declare(strict_types=1);

namespace Zeevx\LaravelBachs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Zeevx\Bachs\Resources\CheckoutSessions checkoutSessions()
 * @method static \Zeevx\Bachs\Resources\Customers customers()
 * @method static \Zeevx\Bachs\Resources\Subscriptions subscriptions()
 *
 * @see \Zeevx\Bachs\Bachs
 */
final class Bachs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Zeevx\Bachs\Bachs::class;
    }
}
