<?php

namespace App\Services;

use Carbon\CarbonInterface;

class ReglaAsistenciaService
{
    public function determinarEstado(
        CarbonInterface $horaActual,
        string $horaEntrada,
        int $tolerancia
    ): string {
        $inicio = $horaActual
            ->copy()
            ->setTimeFromTimeString($horaEntrada);

        $limite = $inicio
            ->copy()
            ->addMinutes($tolerancia);

        if ($horaActual->greaterThan($limite)) {
            return 'AUSENTE';
        }

        if ($horaActual->greaterThan($inicio)) {
            return 'TARDIA';
        }

        return 'PRESENTE';
    }
}