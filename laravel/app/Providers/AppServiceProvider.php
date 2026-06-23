<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register observers for auditing
        \App\Models\Tenant::observe(\App\Observers\TenantObserver::class);
        \App\Models\SmppClient::observe(\App\Observers\AuditObserver::class);
        \App\Models\Message::observe(\App\Observers\AuditObserver::class);
    }
}