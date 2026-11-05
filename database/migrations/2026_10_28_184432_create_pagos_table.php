<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();
            $table->string('metodo');
            $table->unsignedInteger('valor');
            $table->unsignedInteger('propina')->default(0);
            $table->foreignId('registrado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->string('motivo_anulacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
