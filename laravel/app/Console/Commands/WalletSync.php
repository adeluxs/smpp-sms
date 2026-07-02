<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Wallet;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class WalletSync extends Command
{
    protected $signature = 'wallet:sync';
    protected $description = 'Create wallets for tenants without them';

    public function handle(): void
    {
        $tenants = Tenant::doesntHave('wallet')->get();

        foreach ($tenants as $tenant) {
            Wallet::create([
                'tenant_id' => $tenant->id,
                'balance' => 0,
                'type' => 'prepaid',
                'credit_limit' => 0,
            ]);
            $this->info("Created wallet for tenant {$tenant->name}");
        }

        $this->info('Wallet sync complete');
    }
}