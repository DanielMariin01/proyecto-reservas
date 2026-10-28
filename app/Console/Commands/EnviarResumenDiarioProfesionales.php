<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contratos\ServicioDeCorreo;
use App\Estados\Cita\FabricaEstadoCita;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Notifications\ResumenDiarioDeCitasParaProfesional;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * RF-NOT-03: resumen diario por correo al profesional con sus citas del
 * día. Corre una sola vez al día (routes/console.php, dailyAt 07:00), así
 * que no necesita marca de idempotencia: una segunda corrida el mismo día
 * mostraría el mismo resumen, nunca una cita duplicada.
 */
final class EnviarResumenDiarioProfesionales extends Command
{
    protected $signature = 'citas:enviar-resumen-diario-profesionales';

    protected $description = 'Envía a cada profesional con usuario vinculado el resumen de sus citas de hoy.';

    public function handle(FabricaEstadoCita $fabricaEstadoCita, ServicioDeCorreo $servicioDeCorreo): int
    {
        $estadosActivos = array_map(
            fn ($estado): string => $estado->value,
            $fabricaEstadoCita->valoresActivos(),
        );

        $profesionales = Profesional::query()
            ->where('activo', true)
            ->whereNotNull('usuario_id')
            ->get();

        foreach ($profesionales as $profesional) {
            if ($profesional->usuario_id === null) {
                continue;
            }

            $zonaHoraria = Negocio::findOrFail($profesional->negocio_id)->zona_horaria;
            $inicioDiaUtc = CarbonImmutable::now($zonaHoraria)->startOfDay()->utc();
            $finDiaUtc = CarbonImmutable::now($zonaHoraria)->endOfDay()->utc();

            $citasDeHoy = Cita::query()
                ->where('profesional_id', $profesional->id)
                ->whereIn('estado', $estadosActivos)
                ->where('inicio', '>=', $inicioDiaUtc)
                ->where('inicio', '<=', $finDiaUtc)
                ->orderBy('inicio')
                ->get();

            $servicioDeCorreo->enviarA(
                Usuario::findOrFail($profesional->usuario_id),
                new ResumenDiarioDeCitasParaProfesional($profesional, $citasDeHoy),
            );
        }

        $this->info("Resúmenes enviados: {$profesionales->count()}");

        return self::SUCCESS;
    }
}
