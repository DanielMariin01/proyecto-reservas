<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('foto')->nullable();
            $table->unsignedInteger('precio');
            $table->boolean('visible')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
