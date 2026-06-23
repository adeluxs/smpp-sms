<?php

use App\Http\Controllers\Api\V1\SmsController;
use App\Http\Controllers\Internal\DLRController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HealthController;

Route::group([
    'prefix' => 'api/v1',
    'middleware' => ['auth:sanctum', 'throttle:api'],
], function () {
    Route::post('/send', [SmsController::class, 'send']);
    Route::get('/messages/{id}', [SmsController::class, 'status']);
    Route::get('/reports/messages', [ReportController::class, 'messages']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);
});

Route::get('/health', HealthController::class);

Route::post('/internal/api/v1/dlr/update', [DLRController::class, 'update']);