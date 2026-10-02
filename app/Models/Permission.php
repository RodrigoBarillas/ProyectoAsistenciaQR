<?php

namespace App\Models;

use App\Enums\PermissionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Roles that have this permission.
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Cast nombre to PermissionEnum.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'nombre' => PermissionEnum::class,
        ];
    }
}
