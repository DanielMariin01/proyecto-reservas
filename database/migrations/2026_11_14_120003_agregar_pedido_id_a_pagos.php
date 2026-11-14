<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('cita_id')->nullable()->change();
            $table->foreignId('pedido_id')->nullable()->after('cita_id')->constrained('pedidos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pedido_id');
            $table->foreignId('cita_id')->nullable(false)->change();
        });
    }
};
