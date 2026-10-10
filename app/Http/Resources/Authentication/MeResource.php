<?php

namespace App\Http\Resources\Authentication;

use App\Http\Resources\EstudianteResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->nombre,
            // Solo presente (y no null) cuando el usuario es un Alumno con
            // perfil de Estudiante asociado. Para Docente/Admin queda null.
            'estudiante' => $this->estudiante ? new EstudianteResource($this->estudiante) : null,
        ];
    }
}
