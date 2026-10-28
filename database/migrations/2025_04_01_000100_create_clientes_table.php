<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('celular');
            $table->string('correo')->nullable();
            $table->text('notas')->nullable();
            $table->unsignedInteger('veces_no_asistio')->default(0);
            $table->timestamp('consentimiento_datos_en')->nullable();
            $table->timestamps();

            $table->unique(['negocio_id', 'celular']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
