<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->date('fecha');

            // Seccion 2: informacion importante para el servicio.
            $table->boolean('alergias')->default(false);
            $table->boolean('reaccion_anterior')->default(false);
            $table->boolean('diabetes')->default(false);
            $table->boolean('problemas_circulacion')->default(false);
            $table->boolean('medicamentos')->default(false);
            $table->boolean('heridas_actuales')->default(false);
            $table->text('otra_condicion')->nullable();
            $table->text('detalle_reaccion')->nullable();

            // Seccion 3: valoracion visual (multiples casillas, uso profesional).
            $table->json('piel');
            $table->json('una');
            $table->json('hallazgos_visibles');
            $table->text('hallazgo_otro_texto')->nullable();
            $table->text('observaciones_valoracion')->nullable();
            $table->string('decision');
            $table->text('motivo_decision')->nullable();

            // Seccion 4: diseno elegido.
            $table->string('servicio_diseno');
            $table->text('servicio_diseno_otro_texto')->nullable();
            $table->string('forma_una');
            $table->string('largo_una');
            $table->text('colores')->nullable();
            $table->text('decoracion_referencia')->nullable();

            // Seccion 5: registro tecnico del servicio.
            $table->time('hora_inicio');
            $table->time('hora_final');
            $table->text('tecnica_realizada')->nullable();
            $table->text('productos_marcas')->nullable();
            $table->string('lote_vencimiento')->nullable();
            $table->boolean('retiro_previo')->default(false);
            $table->boolean('reparaciones')->default(false);
            $table->text('reparaciones_detalle')->nullable();
            $table->text('cambios_durante_servicio')->nullable();
            $table->text('novedades_incidentes')->nullable();

            // Seccion 6: pago y revision.
            $table->unsignedInteger('valor')->default(0);
            $table->unsignedInteger('abono')->default(0);
            $table->unsignedInteger('saldo')->default(0);
            $table->boolean('revisio_resultado')->default(false);
            $table->boolean('recibio_cuidados')->default(false);
            $table->date('fecha_seguimiento')->nullable();
            $table->date('proximo_mantenimiento_sugerido')->nullable();
            $table->boolean('novedad_pendiente')->default(false);

            // Seccion 7: consentimiento y autorizaciones.
            $table->timestamp('consentimiento_en')->nullable();
            $table->string('autorizacion_fotos');
            $table->string('firma_clienta_path')->nullable();
            $table->string('firma_profesional_path')->nullable();

            $table->timestamps();

            $table->index(['negocio_id', 'cliente_id']);
            $table->index(['negocio_id', 'profesional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};
