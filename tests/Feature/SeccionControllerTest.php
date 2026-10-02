<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Seccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lista_secciones_paginadas(): void
    {
        Seccion::factory()->count(3)->create();

        $response = $this->getJson('/api/secciones');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_index_filtra_por_grado_id(): void
    {
        $grado = Grado::factory()->create();
        Seccion::factory()->count(2)->create(['grado_id' => $grado->id]);
        Seccion::factory()->create();

        $response = $this->getJson("/api/secciones?grado_id={$grado->id}");

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_index_filtra_por_estado(): void
    {
        Seccion::factory()->count(2)->create();
        Seccion::factory()->inactiva()->create();

        $response = $this->getJson('/api/secciones?estado=false');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_store_crea_una_seccion(): void
    {
        $grado = Grado::factory()->create();

        $response = $this->postJson('/api/secciones', ['nombre' => 'A', 'grado_id' => $grado->id]);

        $response->assertCreated()->assertJsonPath('data.nombre', 'A');
        $this->assertDatabaseHas('secciones', ['nombre' => 'A', 'grado_id' => $grado->id]);
    }

    public function test_store_permite_el_mismo_nombre_en_grados_distintos(): void
    {
        $gradoUno = Grado::factory()->create();
        $gradoDos = Grado::factory()->create();
        Seccion::factory()->create(['nombre' => 'A', 'grado_id' => $gradoUno->id]);

        $response = $this->postJson('/api/secciones', ['nombre' => 'A', 'grado_id' => $gradoDos->id]);

        $response->assertCreated();
    }

    public function test_store_rechaza_nombre_duplicado_en_el_mismo_grado(): void
    {
        $grado = Grado::factory()->create();
        Seccion::factory()->create(['nombre' => 'A', 'grado_id' => $grado->id]);

        $response = $this->postJson('/api/secciones', ['nombre' => 'A', 'grado_id' => $grado->id]);

        $response->assertUnprocessable()->assertJsonValidationErrors('nombre');
    }

    public function test_store_rechaza_grado_inexistente(): void
    {
        $response = $this->postJson('/api/secciones', ['nombre' => 'A', 'grado_id' => 999]);

        $response->assertUnprocessable()->assertJsonValidationErrors('grado_id');
    }

    public function test_store_rechaza_grado_inactivo(): void
    {
        $grado = Grado::factory()->inactivo()->create();

        $response = $this->postJson('/api/secciones', ['nombre' => 'A', 'grado_id' => $grado->id]);

        $response->assertUnprocessable()->assertJsonValidationErrors('grado_id');
    }

    public function test_show_devuelve_la_seccion(): void
    {
        $seccion = Seccion::factory()->create();

        $response = $this->getJson("/api/secciones/{$seccion->id}");

        $response->assertOk()->assertJsonPath('data.id', $seccion->id);
    }

    public function test_show_devuelve_404_si_no_existe(): void
    {
        $response = $this->getJson('/api/secciones/999');

        $response->assertNotFound();
    }

    public function test_update_edita_la_seccion(): void
    {
        $seccion = Seccion::factory()->create(['nombre' => 'A']);

        $response = $this->putJson("/api/secciones/{$seccion->id}", [
            'nombre' => 'B',
            'grado_id' => $seccion->grado_id,
        ]);

        $response->assertOk()->assertJsonPath('data.nombre', 'B');
    }

    public function test_update_permite_conservar_el_propio_nombre(): void
    {
        $seccion = Seccion::factory()->create(['nombre' => 'A']);

        $response = $this->putJson("/api/secciones/{$seccion->id}", [
            'nombre' => 'A',
            'grado_id' => $seccion->grado_id,
        ]);

        $response->assertOk();
    }

    public function test_update_rechaza_grado_inactivo(): void
    {
        $seccion = Seccion::factory()->create();
        $gradoInactivo = Grado::factory()->inactivo()->create();

        $response = $this->putJson("/api/secciones/{$seccion->id}", [
            'nombre' => $seccion->nombre,
            'grado_id' => $gradoInactivo->id,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('grado_id');
    }

    public function test_update_devuelve_404_si_no_existe(): void
    {
        $grado = Grado::factory()->create();

        $response = $this->putJson('/api/secciones/999', ['nombre' => 'A', 'grado_id' => $grado->id]);

        $response->assertNotFound();
    }

    public function test_destroy_inactiva_la_seccion_sin_borrar_la_fila(): void
    {
        $seccion = Seccion::factory()->create();

        $response = $this->deleteJson("/api/secciones/{$seccion->id}");

        $response->assertOk()->assertJsonPath('data.estado', false);
        $this->assertDatabaseHas('secciones', ['id' => $seccion->id, 'estado' => false]);
    }

    public function test_destroy_devuelve_404_si_no_existe(): void
    {
        $response = $this->deleteJson('/api/secciones/999');

        $response->assertNotFound();
    }

    public function test_destroy_falla_si_tiene_estudiantes_activos(): void
    {
        $seccion = Seccion::factory()->create();
        Estudiante::factory()->create(['seccion_id' => $seccion->id, 'estado' => true]);

        $response = $this->deleteJson("/api/secciones/{$seccion->id}");

        $response->assertUnprocessable()->assertJsonValidationErrors('estado');
        $this->assertDatabaseHas('secciones', ['id' => $seccion->id, 'estado' => true]);
    }
}
