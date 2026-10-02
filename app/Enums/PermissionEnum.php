<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // ── Users ────────────────────────────────────────────────────────────────
    case USER_VIEW   = 'user.view';
    case USER_CREATE = 'user.create';
    case USER_EDIT   = 'user.edit';
    case USER_DELETE = 'user.delete';

    // ── Roles ────────────────────────────────────────────────────────────────
    case ROLE_VIEW   = 'role.view';
    case ROLE_ASSIGN = 'role.assign';

    // ── Grados ───────────────────────────────────────────────────────────────
    case GRADO_VIEW   = 'grado.view';
    case GRADO_CREATE = 'grado.create';
    case GRADO_EDIT   = 'grado.edit';
    case GRADO_DELETE = 'grado.delete';

    // ── Secciones ────────────────────────────────────────────────────────────
    case SECCION_VIEW   = 'seccion.view';
    case SECCION_CREATE = 'seccion.create';
    case SECCION_EDIT   = 'seccion.edit';
    case SECCION_DELETE = 'seccion.delete';

    // ── Estudiantes ──────────────────────────────────────────────────────────
    case ESTUDIANTE_VIEW   = 'estudiante.view';
    case ESTUDIANTE_CREATE = 'estudiante.create';
    case ESTUDIANTE_EDIT   = 'estudiante.edit';
    case ESTUDIANTE_DELETE = 'estudiante.delete';

    // ── Asistencias ──────────────────────────────────────────────────────────
    case ASISTENCIA_VIEW   = 'asistencia.view';
    case ASISTENCIA_MARK   = 'asistencia.mark';
    case ASISTENCIA_EDIT   = 'asistencia.edit';
    case ASISTENCIA_REPORT = 'asistencia.report';

    /**
     * Human-readable description of the permission.
     */
    public function description(): string
    {
        return match($this) {
            self::USER_VIEW   => 'View users.',
            self::USER_CREATE => 'Create users.',
            self::USER_EDIT   => 'Edit users.',
            self::USER_DELETE => 'Delete users.',

            self::ROLE_VIEW   => 'View roles.',
            self::ROLE_ASSIGN => 'Assign roles to users.',

            self::GRADO_VIEW   => 'View grades.',
            self::GRADO_CREATE => 'Create grades.',
            self::GRADO_EDIT   => 'Edit grades.',
            self::GRADO_DELETE => 'Delete grades.',

            self::SECCION_VIEW   => 'View sections.',
            self::SECCION_CREATE => 'Create sections.',
            self::SECCION_EDIT   => 'Edit sections.',
            self::SECCION_DELETE => 'Delete sections.',

            self::ESTUDIANTE_VIEW   => 'View students.',
            self::ESTUDIANTE_CREATE => 'Create students.',
            self::ESTUDIANTE_EDIT   => 'Edit students.',
            self::ESTUDIANTE_DELETE => 'Delete students.',

            self::ASISTENCIA_VIEW   => 'View attendance records.',
            self::ASISTENCIA_MARK   => 'Mark attendance.',
            self::ASISTENCIA_EDIT   => 'Edit attendance records.',
            self::ASISTENCIA_REPORT => 'Generate attendance reports.',
        };
    }

    /**
     * Permissions assigned to ADMINISTRADOR (full access).
     *
     * @return array<self>
     */
    public static function forAdministrador(): array
    {
        return self::cases();
    }

    /**
     * Permissions assigned to DOCENTE.
     *
     * @return array<self>
     */
    public static function forDocente(): array
    {
        return [
            self::GRADO_VIEW,
            self::SECCION_VIEW,
            self::ESTUDIANTE_VIEW,
            self::ESTUDIANTE_CREATE,
            self::ESTUDIANTE_EDIT,
            self::ASISTENCIA_VIEW,
            self::ASISTENCIA_MARK,
            self::ASISTENCIA_EDIT,
            self::ASISTENCIA_REPORT,
        ];
    }

    /**
     * Permissions assigned to ALUMNO.
     *
     * @return array<self>
     */
    public static function forAlumno(): array
    {
        return [
            self::ASISTENCIA_VIEW,
        ];
    }
}
