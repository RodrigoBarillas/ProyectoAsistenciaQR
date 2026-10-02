<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'estado'])]
class Grado extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class);
    }
}
