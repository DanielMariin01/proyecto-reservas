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
            $table->unsignedInteger('horas_minimas_cancelacion')->default(2);
            $table->unsignedInteger('horas_minimas_reprogramacion')->default(2);
            $table->unsignedInteger('maximo_reprogramaciones')->default(2);
            $table->boolean('confirmacion_manual')->default(false);
            $table->unsignedInteger('minutos_tolerancia_inasistencia')->default(15);
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn([
                'horas_minimas_cancelacion',
                'horas_minimas_reprogramacion',
                'maximo_reprogramaciones',
                'confirmacion_manual',
                'minutos_tolerancia_inasistencia',
            ]);
        });
    }
};
