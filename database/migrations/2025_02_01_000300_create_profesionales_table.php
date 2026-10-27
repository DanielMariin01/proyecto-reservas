<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('nombre');
            $table->string('foto')->nullable();
            $table->string('especialidad')->nullable();
            $table->text('biografia')->nullable();
            // Pedido en RF-PRO-01 pero ausente en la tabla `professionals` de la
            // sección 10 (discrepancia menor del documento): texto libre, igual
            // que la biografía, sin estructura ni archivos adjuntos.
            $table->text('certificaciones')->nullable();
            $table->string('color')->nullable();
            $table->boolean('activo')->default(true);
            // Sin usar hasta la sincronización de Fase 2 (RF-INT-01); se agrega
            // ahora para no tener que migrar de nuevo esta tabla después.
            $table->string('google_calendar_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesionales');
    }
};
