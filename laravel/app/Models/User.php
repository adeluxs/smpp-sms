<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'tenant_id'];

    protected $hidden = ['password', 'remember_token'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}