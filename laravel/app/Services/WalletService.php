<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function reserve(string $tenantId, float $amount): bool
    {
        return DB::transaction(function () use ($tenantId, $amount) {
            $wallet = Wallet::where('tenant_id', $tenantId)->lockForUpdate()->first();

            if (!$wallet) {
                return false;
            }

            if ($wallet->type === 'prepaid' && $wallet->balance < $amount) {
                return false;
            }

            if ($wallet->type === 'postpaid' && $wallet->balance + $wallet->credit_limit < $amount) {
                return false;
            }

            $wallet->balance -= $amount;
            $wallet->last_transaction_at = now();
            $wallet->save();

            return true;
        });
    }

    public function release(string $tenantId, float $amount): void
    {
        DB::transaction(function () use ($tenantId, $amount) {
            $wallet = Wallet::where('tenant_id', $tenantId)->lockForUpdate()->first();

            if ($wallet) {
                $wallet->balance += $amount;
                $wallet->save();

                Log::info('Credits released', [
                    'tenant_id' => $tenantId,
                    'amount' => $amount,
                ]);
            }
        });
    }

    public function deduct(string $tenantId, float $amount): bool
    {
        return DB::transaction(function () use ($tenantId, $amount) {
            $wallet = Wallet::where('tenant_id', $tenantId)->lockForUpdate()->first();

            if (!$wallet || $wallet->balance < $amount) {
                return false;
            }

            $wallet->balance -= $amount;
            $wallet->save();

            return true;
        });
    }

    public function checkLowBalance(string $tenantId): bool
    {
        $wallet = Wallet::where('tenant_id', $tenantId)->first();

        if (!$wallet) {
            return true;
        }

        return $wallet->balance <= $wallet->low_balance_threshold;
    }
}