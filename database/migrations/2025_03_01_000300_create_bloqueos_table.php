<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->string('tipo');
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index(['profesional_id', 'inicio', 'fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueos');
    }
};
