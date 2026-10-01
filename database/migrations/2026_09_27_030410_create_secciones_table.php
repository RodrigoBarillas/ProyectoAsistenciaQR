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
        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
 
            $table->string('nombre', 20);
 
            $table->foreignId('grado_id')
                ->constrained('grados')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
 
            $table->boolean('estado')->default(true);
 
            $table->timestamps();
 
            $table->unique(['grado_id', 'nombre']);
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secciones');
    }
};