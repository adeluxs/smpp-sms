<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'tenant_id',
        'smpp_client_id',
        'route_id',
        'provider_id',
        'api_key_id',
        'source',
        'destination',
        'content',
        'segments',
        'encoding',
        'status',
        'price',
        'cost',
    ];

    protected $casts = [
        'status' => 'string',
        'encoding' => 'string',
        'price' => 'decimal:4',
        'cost' => 'decimal:4',
        'segments' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function smppClient(): BelongsTo
    {
        return $this->belongsTo(SmppClient::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}