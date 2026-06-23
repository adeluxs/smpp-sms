<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmppClient extends Model
{
    protected $fillable = [
        'tenant_id',
        'system_id',
        'password_hash',
        'sender_id',
        'ip_allowlist',
        'throughput_limit',
        'bind_mode',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'bind_mode' => 'string',
    ];

    protected $hidden = ['password_hash'];
    
    public static $plainPassword;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}