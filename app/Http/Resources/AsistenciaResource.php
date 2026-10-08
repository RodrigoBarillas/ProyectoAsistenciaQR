<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsistenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha_asistencia,
            'hora_entrada' => $this->hora_entrada,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
        ];
    }
}