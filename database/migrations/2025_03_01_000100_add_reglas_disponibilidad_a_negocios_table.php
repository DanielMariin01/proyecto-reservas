<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->unsignedSmallInteger('aviso_minimo_minutos')->default(60);
            $table->unsignedSmallInteger('ventana_maxima_dias')->default(30);
            $table->unsignedSmallInteger('margen_antes_minutos')->default(0);
            $table->unsignedSmallInteger('margen_despues_minutos')->default(0);
            $table->unsignedSmallInteger('intervalo_franjas_minutos')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn([
                'aviso_minimo_minutos',
                'ventana_maxima_dias',
                'margen_antes_minutos',
                'margen_despues_minutos',
                'intervalo_franjas_minutos',
            ]);
        });
    }
};
