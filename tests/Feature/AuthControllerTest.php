<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\Role;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura de GET /auth/me únicamente. login/refresh/logout son de otro
 * módulo (auth) y quedan fuera del alcance de esta tarea.
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_devuelve_el_perfil_de_un_alumno_con_su_estudiante(): void
    {
        $role = Role::create(['nombre' => 'Alumno', 'descripcion' => 'Alumno', 'estado' => true]);
        $user = User::factory()->create(['role_id' => $role->id, 'name' => 'Luis Hernández', 'email' => 'luis@example.com']);
        $seccion = Seccion::factory()->create(['nombre' => 'A']);
        $estudiante = Estudiante::factory()->create([
            'user_id' => $user->id,
            'seccion_id' => $seccion->id,
            'codigo_estudiante' => 'EST-002',
        ]);

        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Luis Hernández')
            ->assertJsonPath('data.email', 'luis@example.com')
            ->assertJsonPath('data.role', 'Alumno')
            ->assertJsonPath('data.estudiante.id', $estudiante->id)
            ->assertJsonPath('data.estudiante.codigo_estudiante', 'EST-002')
            ->assertJsonPath('data.estudiante.seccion.id', $seccion->id)
            ->assertJsonPath('data.estudiante.seccion.grado.id', $seccion->grado_id);
    }

    public function test_me_devuelve_estudiante_null_para_un_docente(): void
    {
        $role = Role::create(['nombre' => 'Docente', 'descripcion' => 'Docente', 'estado' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.role', 'Docente')
            ->assertJsonPath('data.estudiante', null);
    }

    public function test_me_rechaza_a_un_usuario_no_autenticado(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized();
    }
}
