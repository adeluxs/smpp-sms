<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Models\SmppClient;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TenantObserver
{
    public function created(Tenant $tenant): void
    {
        $password = Str::random(16);

        SmppClient::create([
            'tenant_id' => $tenant->id,
            'system_id' => 'client_' . Str::random(8),
            'password_hash' => Hash::make($password),
            'bind_mode' => 'transceiver',
            'status' => 'active',
            'throughput_limit' => 100,
        ]);

        Wallet::create([
            'tenant_id' => $tenant->id,
            'balance' => 10.00,
            'type' => 'prepaid',
        ]);

        // Send welcome email with SMPP credentials (future)
    }
}