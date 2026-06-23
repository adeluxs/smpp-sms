<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Route extends Model
{
    protected $fillable = [
        'name',
        'type',
        'prefix',
        'country_code',
        'priority',
        'max_throughput',
        'provider_id',
        'enabled',
    ];

    protected $casts = [
        'type' => 'string',
        'enabled' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}