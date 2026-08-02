<?php

declare(strict_types=1);

use Zeevx\Bachs\Bachs;
use Zeevx\Bachs\Webhooks\WebhookVerifier;

test('it registers the client and webhook verifier', function () {
    expect(app(Bachs::class))->toBeInstanceOf(Bachs::class)
        ->and(app(WebhookVerifier::class))->toBeInstanceOf(WebhookVerifier::class);
});
