<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zeevx\LaravelBachs\Http\Controllers\WebhookController;
use Zeevx\LaravelBachs\Http\Middleware\VerifyWebhookSignature;

Route::post((string) config('bachs.webhook.path'), WebhookController::class)
    ->middleware(VerifyWebhookSignature::class)
    ->name('bachs.webhook');
