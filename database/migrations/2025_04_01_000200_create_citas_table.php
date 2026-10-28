<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->string('estado');
            $table->unsignedInteger('precio');
            $table->string('origen');
            $table->text('notas')->nullable();
            $table->string('motivo_cancelacion')->nullable();
            $table->string('token_cancelacion')->nullable()->unique();
            $table->unsignedInteger('veces_reprogramada')->default(0);
            $table->timestamps();

            $table->index(['profesional_id', 'inicio', 'fin']);
            $table->index(['negocio_id', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
