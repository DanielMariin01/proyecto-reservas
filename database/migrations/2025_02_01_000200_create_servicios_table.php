<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('categoria_servicio_id')->nullable()->constrained('categorias_servicio')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('foto')->nullable();
            $table->unsignedSmallInteger('duracion_minutos');
            $table->unsignedInteger('precio');
            $table->unsignedSmallInteger('margen_antes_minutos')->nullable();
            $table->unsignedSmallInteger('margen_despues_minutos')->nullable();
            $table->unsignedSmallInteger('intervalo_franjas_minutos')->nullable();
            $table->boolean('visible')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
