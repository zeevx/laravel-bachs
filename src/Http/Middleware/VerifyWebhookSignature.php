<?php

declare(strict_types=1);

namespace Zeevx\LaravelBachs\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Zeevx\Bachs\Webhooks\WebhookVerifier;
use Zeevx\Bachs\Exceptions\WebhookException;
use Symfony\Component\HttpFoundation\Response;

final readonly class VerifyWebhookSignature
{
    public function __construct(private WebhookVerifier $verifier) {}

    public function handle(Request $request, Closure $next): Response
    {
        $timestamp = $request->header('X-Bachs-Timestamp');
        $signature = $request->header('X-Bachs-Signature');

        if (! is_string($timestamp) || ! is_string($signature)) {
            return response()->json(['message' => 'Invalid Bachs webhook signature.'], 400);
        }

        try {
            $event = $this->verifier->verify(
                payload: $request->getContent(),
                timestamp: $timestamp,
                signature: $signature,
            );
        } catch (WebhookException) {
            return response()->json(['message' => 'Invalid Bachs webhook signature.'], 400);
        }

        $request->attributes->set('bachs_webhook_event', $event);

        return $next($request);
    }
}
