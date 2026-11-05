<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('cita_id')->unique()->constrained('citas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->unsignedTinyInteger('calificacion');
            $table->text('comentario');
            $table->string('estado');
            $table->text('respuesta_texto')->nullable();
            $table->timestamp('respondida_en')->nullable();
            $table->timestamp('oculta_en')->nullable();
            $table->foreignId('oculta_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();

            $table->index(['negocio_id', 'estado']);
            $table->index(['profesional_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
