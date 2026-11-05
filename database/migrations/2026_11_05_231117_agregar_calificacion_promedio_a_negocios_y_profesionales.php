<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table): void {
            $table->decimal('promedio_calificacion', 3, 2)->nullable();
            $table->unsignedInteger('total_resenas')->default(0);
        });

        Schema::table('profesionales', function (Blueprint $table): void {
            $table->decimal('promedio_calificacion', 3, 2)->nullable();
            $table->unsignedInteger('total_resenas')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table): void {
            $table->dropColumn(['promedio_calificacion', 'total_resenas']);
        });

        Schema::table('profesionales', function (Blueprint $table): void {
            $table->dropColumn(['promedio_calificacion', 'total_resenas']);
        });
    }
};
