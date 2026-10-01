<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('estudiante_id')
                ->constrained('estudiantes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
 
            $table->date('fecha_asistencia');
 
            $table->time('hora_entrada')->nullable();
 
            $table->enum('estado', [
                'PRESENTE',
                'AUSENTE'
            ]);
 
            $table->string('observaciones', 255)->nullable();
 
            $table->timestamps();
 
            $table->unique([
                'estudiante_id',
                'fecha_asistencia'
            ]);
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};