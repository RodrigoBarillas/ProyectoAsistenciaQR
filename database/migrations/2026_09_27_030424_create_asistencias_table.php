<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('estudiante_id')
                ->constrained('estudiantes')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->date('fecha_asistencia');

            $table->time('hora_entrada')->nullable();

            $table->enum('estado', [
                'PRESENTE',
                'TARDIA',
                'AUSENTE'
            ]);

            $table->string('observaciones', 255)->nullable();

            $table->foreignId('registrado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            // Un estudiante solo puede tener una asistencia por fecha
            $table->unique(
                ['estudiante_id', 'fecha_asistencia'],
                'asistencias_estudiante_fecha_unique'
            );

            // Índices adicionales
            $table->index(
                'fecha_asistencia',
                'asistencias_fecha_index'
            );

            $table->index(
                'estado',
                'asistencias_estado_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};