<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reemplaza los hex libres de color_primario/color_secundario por una
     * referencia a la libreria de paletas (paletas_marca): el proyecto aun
     * no tiene negocios reales en produccion, asi que no hace falta migrar
     * datos existentes.
     */
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn(['color_primario', 'color_secundario']);
            $table->foreignId('paleta_marca_id')->nullable()->after('logo')->constrained('paletas_marca')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paleta_marca_id');
            $table->string('color_primario')->nullable();
            $table->string('color_secundario')->nullable();
        });
    }
};
