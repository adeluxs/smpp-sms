<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Provider extends Model
{
    protected $fillable = [
        'name',
        'type',
        'host',
        'port',
        'system_id',
        'throughput_limit',
        'status',
        'capabilities',
        'api_url',
    ];

    protected $casts = [
        'status' => 'string',
        'type' => 'string',
        'capabilities' => 'array',
    ];

    public function getPasswordAttribute(): ?string
    {
        return $this->password_encrypted ? Crypt::decryptString($this->password_encrypted) : null;
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->password_encrypted = Crypt::encryptString($value);
    }

    public function getApiKeyAttribute(): ?string
    {
        return $this->api_key_encrypted ? Crypt::decryptString($this->api_key_encrypted) : null;
    }

    public function setApiKeyAttribute(string $value): void
    {
        $this->api_key_encrypted = Crypt::encryptString($value);
    }

    public function isHttp(): bool
    {
        return $this->type === 'http';
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}