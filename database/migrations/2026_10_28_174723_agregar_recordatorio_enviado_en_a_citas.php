<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table): void {
            $table->timestamp('recordatorio_enviado_en')->nullable()->after('token_cancelacion');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table): void {
            $table->dropColumn('recordatorio_enviado_en');
        });
    }
};
