<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_error', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->nullable()->constrained('negocios')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->uuid('id_peticion')->nullable();
            $table->string('codigo_referencia');
            $table->string('clase_excepcion');
            $table->text('mensaje');
            $table->string('ruta')->nullable();
            $table->string('metodo_http', 10)->nullable();
            $table->unsignedSmallInteger('estado_http');
            $table->timestamp('created_at')->nullable();

            $table->index(['negocio_id', 'created_at']);
            $table->index(['clase_excepcion', 'ruta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_error');
    }
};
