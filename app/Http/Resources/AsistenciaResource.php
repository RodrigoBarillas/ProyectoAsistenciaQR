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
            // Solo presente cuando el controlador hace eager load de
            // 'estudiante' (p. ej. el reporte agregado para Docente/Admin,
            // donde se ve la asistencia de varios estudiantes a la vez). El
            // self-view del alumno no la carga y el campo queda ausente.
            'estudiante' => new EstudianteResource($this->whenLoaded('estudiante')),
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
