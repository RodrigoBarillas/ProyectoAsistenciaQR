<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Horario;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Tests\Concerns\AuthenticatesWithPermissions;
use Tests\TestCase;

class AsistenciaControllerTest extends TestCase
{
    use RefreshDatabase, AuthenticatesWithPermissions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Authenticate as a student (role "Alumno") with an active Estudiante
     * profile linked to the user. AsistenciaController::registrar()/historial()
     * resolve the student by role name + estudiantes.user_id, not just by
     * permission, so the shared AuthenticatesWithPermissions helper (which
     * creates a generic test role) doesn't fit these flows.
     */
    private function actingAsAlumno(array $permissions, array $estudianteOverrides = []): Estudiante
    {
        $role = Role::firstOrCreate(
            ['nombre' => RoleEnum::ALUMNO->value],
            ['descripcion' => 'Alumno', 'estado' => true],
        );

        foreach ($permissions as $permission) {
            $role->permissions()->syncWithoutDetaching(
                Permission::firstOrCreate(['nombre' => $permission])->id
            );
        }

        $user = User::factory()->create(['role_id' => $role->id]);

        $estudiante = Estudiante::factory()->create(array_merge([
            'user_id' => $user->id,
            'estado' => true,
        ], $estudianteOverrides));

        $this->actingAs($user, 'api');

        return $estudiante;
    }

    private function qrTokenPara(Seccion $seccion, ?string $fecha = null): string
    {
        return Crypt::encrypt([
            'seccion_id' => $seccion->id,
            'horario_id' => $seccion->horario_id,
            'fecha' => $fecha ?? now()->toDateString(),
        ]);
    }

    // ── generarQr ────────────────────────────────────────────────────────────

    public function test_generar_qr_devuelve_imagen_para_seccion_activa_con_horario(): void
    {
        if (! extension_loaded('gd') || ! extension_loaded('imagick')) {
            // simplesoftwareio/simple-qrcode 4.2.0 usa Imagick (no gd) para
            // renderizar PNG — ver Generator::getFormatter(). gd solo hace
            // falta para el merge del logo/degradado. Sin ambas, no hay forma
            // de generar la imagen real en este entorno.
            $this->markTestSkipped('Las extensiones ext-gd y ext-imagick no están ambas habilitadas en este entorno; simplesoftwareio/simple-qrcode las requiere para generar la imagen PNG.');
        }

        $horario = Horario::factory()->create();
        $seccion = Seccion::factory()->create(['horario_id' => $horario->id, 'estado' => true]);

        $this->actingAsUserWithPermissions(['asistencia.mark']);

        $response = $this->getJson("/api/v1/asistencias/generar-qr/{$seccion->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['qr_base64', 'seccion' => ['id', 'nombre', 'horario']]]);
    }

    public function test_generar_qr_falla_si_la_seccion_no_existe(): void
    {
        $this->actingAsUserWithPermissions(['asistencia.mark']);

        $response = $this->getJson('/api/v1/asistencias/generar-qr/999');

        $response->assertStatus(400);
    }

    public function test_generar_qr_falla_si_la_seccion_no_tiene_horario_activo(): void
    {
        $seccion = Seccion::factory()->create(['horario_id' => null, 'estado' => true]);

        $this->actingAsUserWithPermissions(['asistencia.mark']);

        $response = $this->getJson("/api/v1/asistencias/generar-qr/{$seccion->id}");

        $response->assertStatus(400);
    }

    // ── registrar ────────────────────────────────────────────────────────────

    public function test_registrar_marca_presente_dentro_de_la_hora_de_entrada(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 8, 7, 0, 0));

        $horario = Horario::factory()->create(['hora_entrada' => '07:00:00', 'tolerancia' => 10]);
        $seccion = Seccion::factory()->create(['horario_id' => $horario->id]);
        $estudiante = $this->actingAsAlumno(['asistencia.mark'], ['seccion_id' => $seccion->id]);

        $response = $this->postJson('/api/v1/asistencias/registrar', [
            'qr_token' => $this->qrTokenPara($seccion),
        ]);

        $response->assertOk()->assertJsonPath('data.estado', 'PRESENTE');

        $this->assertDatabaseHas('asistencias', [
            'estudiante_id' => $estudiante->id,
            'estado' => 'PRESENTE',
        ]);
    }

    public function test_registrar_marca_tardia_dentro_de_la_tolerancia(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 8, 7, 5, 0));

        $horario = Horario::factory()->create(['hora_entrada' => '07:00:00', 'tolerancia' => 10]);
        $seccion = Seccion::factory()->create(['horario_id' => $horario->id]);
        $this->actingAsAlumno(['asistencia.mark'], ['seccion_id' => $seccion->id]);

        $response = $this->postJson('/api/v1/asistencias/registrar', [
            'qr_token' => $this->qrTokenPara($seccion),
        ]);

        $response->assertOk()->assertJsonPath('data.estado', 'TARDIA');
    }

    public function test_registrar_marca_ausente_fuera_de_la_tolerancia_pero_persiste_el_registro(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 8, 7, 15, 0));

        $horario = Horario::factory()->create(['hora_entrada' => '07:00:00', 'tolerancia' => 10]);
        $seccion = Seccion::factory()->create(['horario_id' => $horario->id]);
        $estudiante = $this->actingAsAlumno(['asistencia.mark'], ['seccion_id' => $seccion->id]);

        $response = $this->postJson('/api/v1/asistencias/registrar', [
            'qr_token' => $this->qrTokenPara($seccion),
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('asistencias', [
            'estudiante_id' => $estudiante->id,
            'estado' => 'AUSENTE',
        ]);
    }

    public function test_registrar_rechaza_a_un_usuario_que_no_es_alumno(): void
    {
        $horario = Horario::factory()->create();
        $seccion = Seccion::factory()->create(['horario_id' => $horario->id]);

        $this->actingAsUserWithPermissions(['asistencia.mark']);

        $response = $this->postJson('/api/v1/asistencias/registrar', [
            'qr_token' => $this->qrTokenPara($seccion),
        ]);

        $response->assertStatus(403);
    }

    public function test_registrar_rechaza_qr_de_una_seccion_distinta_a_la_del_estudiante(): void
    {
        $horarioA = Horario::factory()->create();
        $seccionA = Seccion::factory()->create(['horario_id' => $horarioA->id]);

        $horarioB = Horario::factory()->create();
        $seccionB = Seccion::factory()->create(['horario_id' => $horarioB->id]);

        $this->actingAsAlumno(['asistencia.mark'], ['seccion_id' => $seccionA->id]);

        $response = $this->postJson('/api/v1/asistencias/registrar', [
            'qr_token' => $this->qrTokenPara($seccionB),
        ]);

        $response->assertStatus(403);
    }

    public function test_registrar_rechaza_un_qr_corrupto_o_no_cifrado(): void
    {
        $horario = Horario::factory()->create();
        $seccion = Seccion::factory()->create(['horario_id' => $horario->id]);

        $this->actingAsAlumno(['asistencia.mark'], ['seccion_id' => $seccion->id]);

        $response = $this->postJson('/api/v1/asistencias/registrar', [
            'qr_token' => 'esto-no-es-un-payload-cifrado',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('asistencias', 0);
    }

    public function test_registrar_rechaza_un_qr_expirado(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 8, 7, 0, 0));

        $horario = Horario::factory()->create();
        $seccion = Seccion::factory()->create(['horario_id' => $horario->id]);

        $this->actingAsAlumno(['asistencia.mark'], ['seccion_id' => $seccion->id]);

        $response = $this->postJson('/api/v1/asistencias/registrar', [
            'qr_token' => $this->qrTokenPara($seccion, '2026-10-07'),
        ]);

        $response->assertStatus(400);
    }

    public function test_registrar_rechaza_asistencia_duplicada_el_mismo_dia(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 8, 7, 0, 0));

        $horario = Horario::factory()->create(['hora_entrada' => '07:00:00', 'tolerancia' => 10]);
        $seccion = Seccion::factory()->create(['horario_id' => $horario->id]);
        $estudiante = $this->actingAsAlumno(['asistencia.mark'], ['seccion_id' => $seccion->id]);

        Asistencia::factory()->create([
            'estudiante_id' => $estudiante->id,
            'fecha_asistencia' => now()->toDateString(),
        ]);

        $response = $this->postJson('/api/v1/asistencias/registrar', [
            'qr_token' => $this->qrTokenPara($seccion),
        ]);

        $response->assertStatus(409);
        $this->assertDatabaseCount('asistencias', 1);
    }

    // ── historial ────────────────────────────────────────────────────────────

    public function test_historial_lista_las_asistencias_propias_paginadas(): void
    {
        $estudiante = $this->actingAsAlumno(['asistencia.view']);

        Asistencia::factory()->count(3)->create(['estudiante_id' => $estudiante->id]);

        $response = $this->getJson('/api/v1/asistencias/historial');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_historial_no_incluye_asistencias_de_otro_estudiante(): void
    {
        $otro = Estudiante::factory()->create();
        Asistencia::factory()->create(['estudiante_id' => $otro->id]);

        $estudiante = $this->actingAsAlumno(['asistencia.view']);
        Asistencia::factory()->create(['estudiante_id' => $estudiante->id]);

        $response = $this->getJson('/api/v1/asistencias/historial');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_historial_falla_si_el_usuario_no_tiene_un_estudiante_asociado(): void
    {
        $this->actingAsUserWithPermissions(['asistencia.view']);

        $response = $this->getJson('/api/v1/asistencias/historial');

        $response->assertNotFound();
    }

    // ── reporte ──────────────────────────────────────────────────────────────

    public function test_reporte_lista_asistencias_de_todos_los_estudiantes_con_su_info(): void
    {
        $estudianteA = Estudiante::factory()->create();
        $estudianteB = Estudiante::factory()->create();
        Asistencia::factory()->create(['estudiante_id' => $estudianteA->id]);
        Asistencia::factory()->create(['estudiante_id' => $estudianteB->id]);

        $this->actingAsUserWithPermissions(['asistencia.report']);

        $response = $this->getJson('/api/v1/asistencias/reporte');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'estudiante' => ['id', 'nombres', 'apellidos']]], 'links', 'meta']);
    }

    public function test_reporte_filtra_por_seccion(): void
    {
        $seccionA = Seccion::factory()->create();
        $seccionB = Seccion::factory()->create();
        $estudianteA = Estudiante::factory()->create(['seccion_id' => $seccionA->id]);
        $estudianteB = Estudiante::factory()->create(['seccion_id' => $seccionB->id]);
        Asistencia::factory()->create(['estudiante_id' => $estudianteA->id]);
        Asistencia::factory()->create(['estudiante_id' => $estudianteB->id]);

        $this->actingAsUserWithPermissions(['asistencia.report']);

        $response = $this->getJson("/api/v1/asistencias/reporte?seccion_id={$seccionA->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estudiante_id', $estudianteA->id);
    }

    public function test_reporte_filtra_por_grado(): void
    {
        $grado = Grado::factory()->create();
        $seccionDelGrado = Seccion::factory()->create(['grado_id' => $grado->id]);
        $otraSeccion = Seccion::factory()->create();
        $estudianteA = Estudiante::factory()->create(['seccion_id' => $seccionDelGrado->id]);
        $estudianteB = Estudiante::factory()->create(['seccion_id' => $otraSeccion->id]);
        Asistencia::factory()->create(['estudiante_id' => $estudianteA->id]);
        Asistencia::factory()->create(['estudiante_id' => $estudianteB->id]);

        $this->actingAsUserWithPermissions(['asistencia.report']);

        $response = $this->getJson("/api/v1/asistencias/reporte?grado_id={$grado->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estudiante_id', $estudianteA->id);
    }

    public function test_reporte_filtra_por_estado(): void
    {
        $estudiante = Estudiante::factory()->create();
        Asistencia::factory()->create(['estudiante_id' => $estudiante->id, 'estado' => 'PRESENTE']);
        Asistencia::factory()->ausente()->create(['estudiante_id' => $estudiante->id]);

        $this->actingAsUserWithPermissions(['asistencia.report']);

        $response = $this->getJson('/api/v1/asistencias/reporte?estado=AUSENTE');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estado', 'AUSENTE');
    }

    public function test_reporte_filtra_por_rango_de_fechas(): void
    {
        $estudiante = Estudiante::factory()->create();
        Asistencia::factory()->create(['estudiante_id' => $estudiante->id, 'fecha_asistencia' => '2026-01-10']);
        Asistencia::factory()->create(['estudiante_id' => $estudiante->id, 'fecha_asistencia' => '2026-06-15']);

        $this->actingAsUserWithPermissions(['asistencia.report']);

        $response = $this->getJson('/api/v1/asistencias/reporte?fecha_desde=2026-01-01&fecha_hasta=2026-01-31');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fecha_asistencia', '2026-01-10');
    }

    public function test_reporte_rechaza_estado_invalido(): void
    {
        $this->actingAsUserWithPermissions(['asistencia.report']);

        $response = $this->getJson('/api/v1/asistencias/reporte?estado=NO_EXISTE');

        $response->assertUnprocessable();
    }

    public function test_reporte_rechaza_a_usuarios_sin_el_permiso_report(): void
    {
        // Alumno tiene asistencia.view/mark, pero no asistencia.report.
        $this->actingAsAlumno(['asistencia.view', 'asistencia.mark']);

        $response = $this->getJson('/api/v1/asistencias/reporte');

        $response->assertForbidden();
    }
}
