<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'tenant_id',
        'balance',
        'type',
        'credit_limit',
        'low_balance_threshold',
    ];

    protected $casts = [
        'type' => 'string',
        'balance' => 'decimal:4',
        'credit_limit' => 'decimal:4',
        'low_balance_threshold' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}