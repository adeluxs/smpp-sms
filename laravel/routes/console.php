<?php

use App\Console\Commands\WalletSync;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you can define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('wallet:sync', function () {
    $tenants = \App\Models\Tenant::doesntHave('wallet')->get();

    foreach ($tenants as $tenant) {
        \App\Models\Wallet::create([
            'tenant_id' => $tenant->id,
            'balance' => 0,
            'type' => 'prepaid',
            'credit_limit' => 0,
        ]);
        $this->info("Created wallet for tenant {$tenant->name}");
    }

    $this->info('Wallet sync complete');
})->purpose('Create wallets for tenants without them');