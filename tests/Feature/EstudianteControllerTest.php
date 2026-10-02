<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Seccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstudianteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lista_estudiantes_paginados(): void
    {
        Estudiante::factory()->count(3)->create();

        $response = $this->getJson('/api/estudiantes');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_index_filtra_por_seccion_id(): void
    {
        $seccion = Seccion::factory()->create();
        Estudiante::factory()->count(2)->create(['seccion_id' => $seccion->id]);
        Estudiante::factory()->create();

        $response = $this->getJson("/api/estudiantes?seccion_id={$seccion->id}");

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_index_filtra_por_grado_id_a_traves_de_la_seccion(): void
    {
        $grado = Grado::factory()->create();
        $seccion = Seccion::factory()->create(['grado_id' => $grado->id]);
        Estudiante::factory()->count(2)->create(['seccion_id' => $seccion->id]);
        Estudiante::factory()->create();

        $response = $this->getJson("/api/estudiantes?grado_id={$grado->id}");

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_index_filtra_por_estado(): void
    {
        Estudiante::factory()->count(2)->create();
        Estudiante::factory()->inactivo()->create();

        $response = $this->getJson('/api/estudiantes?estado=false');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_index_busca_por_nombre_apellido_y_codigo(): void
    {
        Estudiante::factory()->create(['nombres' => 'Ana Lucía', 'apellidos' => 'Pérez', 'codigo_estudiante' => 'EST-0001']);
        Estudiante::factory()->create(['nombres' => 'Carlos', 'apellidos' => 'Gómez', 'codigo_estudiante' => 'EST-0002']);

        $response = $this->getJson('/api/estudiantes?search=Ana');

        $response->assertOk()->assertJsonCount(1, 'data');

        $response = $this->getJson('/api/estudiantes?search=EST-0002');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_index_respeta_per_page(): void
    {
        Estudiante::factory()->count(5)->create();

        $response = $this->getJson('/api/estudiantes?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_store_crea_un_estudiante_y_genera_qr_token(): void
    {
        $seccion = Seccion::factory()->create();

        $response = $this->postJson('/api/estudiantes', [
            'codigo_estudiante' => 'EST-001',
            'nombres' => 'Ana Lucía',
            'apellidos' => 'Pérez López',
            'seccion_id' => $seccion->id,
        ]);

        $response->assertCreated();
        $qrToken = $response->json('data.qr_token');
        $this->assertNotEmpty($qrToken);
        $this->assertDatabaseHas('estudiantes', ['codigo_estudiante' => 'EST-001', 'qr_token' => $qrToken]);
    }

    public function test_store_rechaza_codigo_estudiante_duplicado(): void
    {
        $seccion = Seccion::factory()->create();
        Estudiante::factory()->create(['codigo_estudiante' => 'EST-001', 'seccion_id' => $seccion->id]);

        $response = $this->postJson('/api/estudiantes', [
            'codigo_estudiante' => 'EST-001',
            'nombres' => 'Ana',
            'apellidos' => 'Pérez',
            'seccion_id' => $seccion->id,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('codigo_estudiante');
    }

    public function test_store_rechaza_seccion_inactiva(): void
    {
        $seccion = Seccion::factory()->inactiva()->create();

        $response = $this->postJson('/api/estudiantes', [
            'codigo_estudiante' => 'EST-001',
            'nombres' => 'Ana',
            'apellidos' => 'Pérez',
            'seccion_id' => $seccion->id,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('seccion_id');
    }

    public function test_store_ignora_qr_token_enviado_por_el_cliente(): void
    {
        $seccion = Seccion::factory()->create();

        $response = $this->postJson('/api/estudiantes', [
            'codigo_estudiante' => 'EST-001',
            'nombres' => 'Ana',
            'apellidos' => 'Pérez',
            'seccion_id' => $seccion->id,
            'qr_token' => 'token-falso-enviado-por-el-cliente',
        ]);

        $response->assertCreated();
        $this->assertNotEquals('token-falso-enviado-por-el-cliente', $response->json('data.qr_token'));
    }

    public function test_show_devuelve_el_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create();

        $response = $this->getJson("/api/estudiantes/{$estudiante->id}");

        $response->assertOk()->assertJsonPath('data.id', $estudiante->id);
    }

    public function test_show_devuelve_404_si_no_existe(): void
    {
        $response = $this->getJson('/api/estudiantes/999');

        $response->assertNotFound();
    }

    public function test_update_edita_el_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create();

        $response = $this->putJson("/api/estudiantes/{$estudiante->id}", [
            'codigo_estudiante' => $estudiante->codigo_estudiante,
            'nombres' => 'Nuevo Nombre',
            'apellidos' => $estudiante->apellidos,
            'seccion_id' => $estudiante->seccion_id,
        ]);

        $response->assertOk()->assertJsonPath('data.nombres', 'Nuevo Nombre');
    }

    public function test_update_no_modifica_el_qr_token(): void
    {
        $estudiante = Estudiante::factory()->create();
        $qrTokenOriginal = $estudiante->qr_token;

        $response = $this->putJson("/api/estudiantes/{$estudiante->id}", [
            'codigo_estudiante' => $estudiante->codigo_estudiante,
            'nombres' => $estudiante->nombres,
            'apellidos' => $estudiante->apellidos,
            'seccion_id' => $estudiante->seccion_id,
            'qr_token' => 'otro-token-distinto',
        ]);

        $response->assertOk()->assertJsonPath('data.qr_token', $qrTokenOriginal);
    }

    public function test_update_rechaza_seccion_inactiva(): void
    {
        $estudiante = Estudiante::factory()->create();
        $seccionInactiva = Seccion::factory()->inactiva()->create();

        $response = $this->putJson("/api/estudiantes/{$estudiante->id}", [
            'codigo_estudiante' => $estudiante->codigo_estudiante,
            'nombres' => $estudiante->nombres,
            'apellidos' => $estudiante->apellidos,
            'seccion_id' => $seccionInactiva->id,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('seccion_id');
    }

    public function test_update_devuelve_404_si_no_existe(): void
    {
        $seccion = Seccion::factory()->create();

        $response = $this->putJson('/api/estudiantes/999', [
            'codigo_estudiante' => 'EST-999',
            'nombres' => 'X',
            'apellidos' => 'Y',
            'seccion_id' => $seccion->id,
        ]);

        $response->assertNotFound();
    }

    public function test_destroy_inactiva_el_estudiante_sin_borrar_la_fila(): void
    {
        $estudiante = Estudiante::factory()->create();

        $response = $this->deleteJson("/api/estudiantes/{$estudiante->id}");

        $response->assertOk()->assertJsonPath('data.estado', false);
        $this->assertDatabaseHas('estudiantes', ['id' => $estudiante->id, 'estado' => false]);
    }

    public function test_destroy_devuelve_404_si_no_existe(): void
    {
        $response = $this->deleteJson('/api/estudiantes/999');

        $response->assertNotFound();
    }

    public function test_busqueda_por_qr_token_devuelve_el_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create();

        $response = $this->getJson("/api/estudiantes/qr/{$estudiante->qr_token}");

        $response->assertOk()->assertJsonPath('data.id', $estudiante->id);
    }

    public function test_busqueda_por_qr_token_devuelve_404_si_no_existe(): void
    {
        $response = $this->getJson('/api/estudiantes/qr/00000000-0000-0000-0000-000000000000');

        $response->assertNotFound();
    }
}
