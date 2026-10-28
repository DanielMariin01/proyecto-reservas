<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contratos\ServicioDeCorreo;
use App\Estados\Cita\FabricaEstadoCita;
use App\Models\Cita;
use App\Models\Cliente;
use App\Notifications\RecordatorioDeCitaCliente;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * RF-NOT-01: recordatorio 24 h antes. Corre cada 15 minutos (ver
 * routes/console.php) y busca citas cuyo inicio cae en una ventana de 15
 * minutos alrededor de "ahora + 24 h"; la columna recordatorio_enviado_en
 * evita que una misma cita reciba el correo dos veces en corridas
 * distintas del cron.
 */
final class EnviarRecordatoriosDeCitas extends Command
{
    private const HORAS_DE_ANTICIPACION = 24;

    private const MINUTOS_DE_VENTANA = 15;

    protected $signature = 'citas:enviar-recordatorios';

    protected $description = 'Envía el correo de recordatorio 24 h antes de cada cita activa.';

    public function handle(FabricaEstadoCita $fabricaEstadoCita, ServicioDeCorreo $servicioDeCorreo): int
    {
        $inicioVentana = CarbonImmutable::now()->addHours(self::HORAS_DE_ANTICIPACION);
        $finVentana = $inicioVentana->addMinutes(self::MINUTOS_DE_VENTANA);

        $estadosActivos = array_map(
            fn ($estado): string => $estado->value,
            $fabricaEstadoCita->valoresActivos(),
        );

        $citas = Cita::query()
            ->whereIn('estado', $estadosActivos)
            ->whereNull('recordatorio_enviado_en')
            ->where('inicio', '>=', $inicioVentana)
            ->where('inicio', '<', $finVentana)
            ->get();

        foreach ($citas as $cita) {
            $correoCliente = Cliente::findOrFail($cita->cliente_id)->correo;

            if ($correoCliente !== null) {
                $servicioDeCorreo->enviarAlCorreo($correoCliente, new RecordatorioDeCitaCliente($cita));
            }

            $cita->update(['recordatorio_enviado_en' => CarbonImmutable::now()]);
        }

        $this->info("Recordatorios enviados: {$citas->count()}");

        return self::SUCCESS;
    }
}
