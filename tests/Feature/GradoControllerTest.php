<?php

namespace Tests\Feature;

use App\Models\Grado;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(), 'api');
    }

    public function test_index_lista_grados_paginados(): void
    {
        Grado::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/grados');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_index_filtra_por_estado(): void
    {
        Grado::factory()->count(2)->create();
        Grado::factory()->inactivo()->create();

        $response = $this->getJson('/api/v1/grados?estado=false');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_store_crea_un_grado(): void
    {
        $response = $this->postJson('/api/v1/grados', ['nombre' => 'Primero Básico']);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', 'Primero Básico')
            ->assertJsonPath('data.estado', true);

        $this->assertDatabaseHas('grados', ['nombre' => 'Primero Básico', 'estado' => true]);
    }

    public function test_store_falla_sin_nombre(): void
    {
        $response = $this->postJson('/api/v1/grados', []);

        $response->assertUnprocessable()->assertJsonValidationErrors('nombre');
    }

    public function test_store_falla_con_nombre_duplicado(): void
    {
        Grado::factory()->create(['nombre' => 'Primero Básico']);

        $response = $this->postJson('/api/v1/grados', ['nombre' => 'Primero Básico']);

        $response->assertUnprocessable()->assertJsonValidationErrors('nombre');
    }

    public function test_show_devuelve_el_grado(): void
    {
        $grado = Grado::factory()->create();

        $response = $this->getJson("/api/v1/grados/{$grado->id}");

        $response->assertOk()->assertJsonPath('data.id', $grado->id);
    }

    public function test_show_devuelve_404_si_no_existe(): void
    {
        $response = $this->getJson('/api/v1/grados/999');

        $response->assertNotFound();
    }

    public function test_update_edita_el_grado(): void
    {
        $grado = Grado::factory()->create(['nombre' => 'Primero Básico']);

        $response = $this->putJson("/api/v1/grados/{$grado->id}", ['nombre' => 'Segundo Básico']);

        $response->assertOk()->assertJsonPath('data.nombre', 'Segundo Básico');
        $this->assertDatabaseHas('grados', ['id' => $grado->id, 'nombre' => 'Segundo Básico']);
    }

    public function test_update_falla_con_nombre_duplicado(): void
    {
        Grado::factory()->create(['nombre' => 'Primero Básico']);
        $grado = Grado::factory()->create(['nombre' => 'Segundo Básico']);

        $response = $this->putJson("/api/v1/grados/{$grado->id}", ['nombre' => 'Primero Básico']);

        $response->assertUnprocessable()->assertJsonValidationErrors('nombre');
    }

    public function test_update_devuelve_404_si_no_existe(): void
    {
        $response = $this->putJson('/api/v1/grados/999', ['nombre' => 'X']);

        $response->assertNotFound();
    }

    public function test_destroy_inactiva_el_grado_sin_borrar_la_fila(): void
    {
        $grado = Grado::factory()->create();

        $response = $this->deleteJson("/api/v1/grados/{$grado->id}");

        $response->assertOk()->assertJsonPath('data.estado', false);
        $this->assertDatabaseHas('grados', ['id' => $grado->id, 'estado' => false]);
    }

    public function test_destroy_devuelve_404_si_no_existe(): void
    {
        $response = $this->deleteJson('/api/v1/grados/999');

        $response->assertNotFound();
    }

    public function test_destroy_falla_si_tiene_secciones_activas(): void
    {
        $grado = Grado::factory()->create();
        Seccion::factory()->create(['grado_id' => $grado->id, 'estado' => true]);

        $response = $this->deleteJson("/api/v1/grados/{$grado->id}");

        $response->assertUnprocessable()->assertJsonValidationErrors('estado');
        $this->assertDatabaseHas('grados', ['id' => $grado->id, 'estado' => true]);
    }

    public function test_destroy_permite_inactivar_grado_con_secciones_ya_inactivas(): void
    {
        $grado = Grado::factory()->create();
        Seccion::factory()->inactiva()->create(['grado_id' => $grado->id]);

        $response = $this->deleteJson("/api/v1/grados/{$grado->id}");

        $response->assertOk()->assertJsonPath('data.estado', false);
    }
}
