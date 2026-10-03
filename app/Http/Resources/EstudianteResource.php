<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstudianteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_estudiante' => $this->codigo_estudiante,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'qr_token' => $this->qr_token,
            'seccion_id' => $this->seccion_id,
            'seccion' => new SeccionResource($this->whenLoaded('seccion')),
            'estado' => $this->estado,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
