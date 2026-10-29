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
            $table->boolean('permite_editar_pagina')->default(false)->after('redes_sociales');
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table): void {
            $table->dropColumn('permite_editar_pagina');
        });
    }
};
