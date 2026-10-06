<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencias';

    protected $fillable = [
        'estudiante_id',
        'fecha_asistencia',
        'hora_entrada',
        'estado',
        'observaciones',
        'registrado_por',
    ];
}
