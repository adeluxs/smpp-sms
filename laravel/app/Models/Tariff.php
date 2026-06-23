<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = [
        'provider_id',
        'prefix',
        'country_code',
        'rate',
        'currency',
        'effective_at',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopeForDestination($query, string $destination)
    {
        $prefix = substr($destination, 0, 5);
        return $query->where('prefix', $prefix)
            ->orWhere('prefix', 'default')
            ->latest('effective_at');
    }
}