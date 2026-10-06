<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secciones', function (Blueprint $table) {
            $table->foreignId('horario_id')
                ->nullable()
                ->after('grado_id')
                ->constrained('horarios')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('secciones', function (Blueprint $table) {
            $table->dropForeign(['horario_id']);
            $table->dropColumn('horario_id');
        });
    }
};
