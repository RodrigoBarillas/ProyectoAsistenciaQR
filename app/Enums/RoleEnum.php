<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMINISTRADOR = 'Administrador';
    case DOCENTE       = 'Docente';
    case ALUMNO        = 'Alumno';

    public function description(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'Full system access.',
            self::DOCENTE       => 'Teacher role with class management access.',
            self::ALUMNO        => 'Student role with read-only access.',
        };
    }
}
