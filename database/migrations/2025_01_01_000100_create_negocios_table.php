<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negocios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('tipo');
            $table->string('logo')->nullable();
            $table->string('color_primario')->nullable();
            $table->string('color_secundario')->nullable();
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('telefono')->nullable();
            $table->string('whatsapp')->nullable();
            $table->json('redes_sociales')->nullable();
            $table->string('zona_horaria')->default('America/Bogota');

            $table->string('estado_suscripcion')->default('activo');
            $table->date('proxima_fecha_pago')->nullable();
            $table->unsignedSmallInteger('dias_gracia')->default(5);
            $table->unsignedTinyInteger('meses_para_cancelar')->default(3);
            $table->unsignedTinyInteger('meses_retencion_datos')->default(12);
            $table->timestamp('vencido_desde')->nullable();
            $table->timestamp('suspendido_desde')->nullable();
            $table->timestamp('cancelado_desde')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negocios');
    }
};
