<?php

namespace Tests\Unit;

use App\Services\ReglaAsistenciaService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ReglaAsistenciaServiceTest extends TestCase
{
    public function test_presente_antes_de_la_hora_de_entrada(): void
    {
        $service = new ReglaAsistenciaService();

        $horaActual = Carbon::create(2026, 10, 8, 6, 59, 59);

        $estado = $service->determinarEstado(
            $horaActual,
            '07:00:00',
            10
        );

        $this->assertEquals('PRESENTE', $estado);
    }

    public function test_presente_exactamente_a_la_hora_de_entrada(): void
    {
        $service = new ReglaAsistenciaService();

        $horaActual = Carbon::create(2026, 10, 8, 7, 0, 0);

        $estado = $service->determinarEstado(
            $horaActual,
            '07:00:00',
            10
        );

        $this->assertEquals('PRESENTE', $estado);
    }

    public function test_tardia_despues_de_la_hora_de_entrada(): void
    {
        $service = new ReglaAsistenciaService();

        $horaActual = Carbon::create(2026, 10, 8, 7, 0, 1);

        $estado = $service->determinarEstado(
            $horaActual,
            '07:00:00',
            10
        );

        $this->assertEquals('TARDIA', $estado);
    }

    public function test_tardia_exactamente_en_el_limite_de_tolerancia(): void
    {
        $service = new ReglaAsistenciaService();

        $horaActual = Carbon::create(2026, 10, 8, 7, 10, 0);

        $estado = $service->determinarEstado(
            $horaActual,
            '07:00:00',
            10
        );

        $this->assertEquals('TARDIA', $estado);
    }

    public function test_ausente_despues_del_limite_de_tolerancia(): void
    {
        $service = new ReglaAsistenciaService();

        $horaActual = Carbon::create(2026, 10, 8, 7, 10, 1);

        $estado = $service->determinarEstado(
            $horaActual,
            '07:00:00',
            10
        );

        $this->assertEquals('AUSENTE', $estado);
    }

    public function test_funciona_con_horario_vespertino(): void
{
    $service = new ReglaAsistenciaService();

    $horaActual = Carbon::create(2026, 10, 8, 13, 5, 0);

    $estado = $service->determinarEstado(
        $horaActual,
        '13:00:00',
        10
    );

    $this->assertEquals('TARDIA', $estado);
}
}