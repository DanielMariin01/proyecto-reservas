<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paginas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->unique()->constrained('negocios')->cascadeOnDelete();
            $table->json('bloques');
            $table->json('bloques_publicados')->nullable();
            $table->timestamp('publicada_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paginas');
    }
};
