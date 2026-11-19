<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disenos_favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_cliente_id')->constrained('cuentas_cliente')->cascadeOnDelete();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->string('diseno_id');
            $table->timestamps();

            $table->unique(['cuenta_cliente_id', 'negocio_id', 'diseno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disenos_favoritos');
    }
};
