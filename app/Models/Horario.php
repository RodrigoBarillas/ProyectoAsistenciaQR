<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'estado', 'tolerancia', 'hora_entrada', 'hora_salida'])]
class Horario extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'estado'       => 'boolean',
            'tolerancia'   => 'integer',
            'hora_entrada' => 'string',
            'hora_salida'  => 'string',
        ];
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class);
    }
}
