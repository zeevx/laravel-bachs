<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Zeevx\LaravelBachs\Events\WebhookReceived;

test('it verifies and dispatches webhook events', function () {
    Event::fake([WebhookReceived::class]);
    $payload = json_encode([
        'id' => 'event_123',
        'type' => 'checkout.completed',
        'data' => ['checkout_id' => 'checkout_123'],
    ], JSON_THROW_ON_ERROR);
    $timestamp = (string) time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'webhook_secret');

    $response = $this->call('POST', '/bachs/webhook', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_BACHS_TIMESTAMP' => $timestamp,
        'HTTP_X_BACHS_SIGNATURE' => $signature,
    ], content: $payload);

    $response->assertNoContent();
    Event::assertDispatched(WebhookReceived::class, fn (WebhookReceived $received): bool => $received->event->id === 'event_123');
});

test('it rejects invalid webhook signatures', function () {
    $payload = json_encode([
        'id' => 'event_123',
        'type' => 'checkout.completed',
        'data' => [],
    ], JSON_THROW_ON_ERROR);

    $this->call('POST', '/bachs/webhook', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_BACHS_TIMESTAMP' => (string) time(),
        'HTTP_X_BACHS_SIGNATURE' => 'invalid',
    ], content: $payload)->assertBadRequest();
});
