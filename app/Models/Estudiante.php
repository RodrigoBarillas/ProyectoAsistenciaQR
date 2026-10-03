<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['codigo_estudiante', 'nombres', 'apellidos', 'seccion_id', 'estado'])]
class Estudiante extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }
}
