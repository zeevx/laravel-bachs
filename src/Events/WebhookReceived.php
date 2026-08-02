<?php

declare(strict_types=1);

namespace Zeevx\LaravelBachs\Events;

use Illuminate\Queue\SerializesModels;
use Zeevx\Bachs\Webhooks\WebhookEvent;
use Illuminate\Foundation\Events\Dispatchable;

final class WebhookReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly WebhookEvent $event) {}
}
