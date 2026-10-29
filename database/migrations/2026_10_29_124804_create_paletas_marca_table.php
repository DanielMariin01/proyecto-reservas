<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo global de paletas de marca (no es por negocio): el super
     * admin elige una de aquí para cada negocio en vez de escribir un hex
     * libre. Reutilizable entre negocios de cualquier tipo.
     */
    public function up(): void
    {
        Schema::create('paletas_marca', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('color_primario');
            $table->string('color_secundario');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paletas_marca');
    }
};
