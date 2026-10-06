<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'password', 'role_id', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'status'            => 'boolean',
        ];
    }

    /**
     * Role assigned to this user.
     *
     * @return BelongsTo<Role, User>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Refresh tokens for this user (stored in personal_access_tokens).
     *
     * @return HasMany<RefreshToken>
     */
    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class, 'tokenable_id')
            ->where('tokenable_type', self::class)
            ->where('name', 'refresh_token');
    }

    /**
     * Student profile linked to this user (only present when role is "estudiante").
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Estudiante>
     */
    public function estudiante(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Estudiante::class);
    }

    /**
     * Check if the user's role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role?->hasPermission($permission) ?? false;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}


