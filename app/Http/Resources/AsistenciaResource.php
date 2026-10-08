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
            'estudiante_id' => $this->estudiante_id,
            'fecha_asistencia' => $this->fecha_asistencia,
            'hora_entrada' => $this->hora_entrada,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'registrado_por' => $this->registrado_por,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
