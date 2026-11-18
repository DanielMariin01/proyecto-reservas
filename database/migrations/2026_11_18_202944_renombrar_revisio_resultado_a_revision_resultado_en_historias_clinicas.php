<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "revisio_resultado" fue un error de tipeo desde la migración
     * original (faltaba la "n" de "revisión"); se corrige el nombre de
     * la columna en vez de dejarlo así para siempre.
     */
    public function up(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->renameColumn('revisio_resultado', 'revision_resultado');
        });
    }

    public function down(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->renameColumn('revision_resultado', 'revisio_resultado');
        });
    }
};
