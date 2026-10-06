<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->boolean('estado')->default(true);
            $table->unsignedSmallInteger('tolerancia')->default(0)->comment('Minutes of tolerance allowed');
            $table->time('hora_entrada');
            $table->time('hora_salida');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
