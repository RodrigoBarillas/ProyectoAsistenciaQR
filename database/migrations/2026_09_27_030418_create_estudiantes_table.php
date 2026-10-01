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

        Schema::create('estudiantes', function (Blueprint $table) {

            $table->id();
 
            $table->string('codigo_estudiante', 30)->unique();
 
            $table->string('nombres', 100);
 
            $table->string('apellidos', 100);
 
            $table->uuid('qr_token')->unique();
 
            $table->foreignId('seccion_id')

                ->constrained('secciones')

                ->cascadeOnUpdate()

                ->restrictOnDelete();
 
            $table->boolean('estado')->default(true);
 
            $table->timestamps();

        });

    }
 
    /**

     * Reverse the migrations.

     */

    public function down(): void

    {

        Schema::dropIfExists('estudiantes');

    }
};