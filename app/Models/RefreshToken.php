<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RefreshToken extends Model
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'expires_at',
    ];

    protected $casts = [
        'abilities'  => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Polymorphic owner (e.g. User).
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to only refresh tokens (name discriminator).
     *
     * @param \Illuminate\Database\Eloquent\Builder<RefreshToken> $query
     * @return \Illuminate\Database\Eloquent\Builder<RefreshToken>
     */
    public function scopeRefreshTokens($query)
    {
        return $query->where('name', 'refresh_token');
    }

    /**
     * Whether this token is still valid.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
