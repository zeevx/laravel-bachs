<?php

declare(strict_types=1);

namespace Zeevx\LaravelBachs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Zeevx\Bachs\Webhooks\WebhookEvent;
use Zeevx\LaravelBachs\Events\WebhookReceived;

final class WebhookController
{
    public function __invoke(Request $request): Response
    {
        $event = $request->attributes->get('bachs_webhook_event');

        abort_unless($event instanceof WebhookEvent, 400, 'The verified Bachs webhook event is missing.');

        WebhookReceived::dispatch($event);

        return response()->noContent();
    }
}
