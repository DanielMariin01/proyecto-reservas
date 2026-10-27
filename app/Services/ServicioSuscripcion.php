<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\CambiarEstadoNegocio;
use App\DatosTransferencia\DatosCambioEstadoNegocio;
use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class ServicioSuscripcion
{
    private const MOTIVO_AUTOMATICO = 'Cambio automático por vencimiento de fechas (tarea programada diaria).';

    public function __construct(
        private readonly CambiarEstadoNegocio $cambiarEstadoNegocio,
    ) {}

    /**
     * Revisa cada negocio no cancelado y lo transiciona si ya cumplió el
     * plazo correspondiente a su estado actual (RF-SUS-02).
     */
    public function actualizarEstados(): void
    {
        Negocio::query()
            ->whereIn('estado_suscripcion', [
                EstadoSuscripcion::Activo,
                EstadoSuscripcion::Vencido,
                EstadoSuscripcion::Suspendido,
            ])
            ->each(function (Negocio $negocio): void {
                $estadoDestino = $this->calcularSiguienteEstadoAutomatico($negocio);

                if ($estadoDestino === null) {
                    return;
                }

                $this->cambiarEstadoNegocio->ejecutar(new DatosCambioEstadoNegocio(
                    negocio: $negocio,
                    estadoDestino: $estadoDestino,
                    motivo: self::MOTIVO_AUTOMATICO,
                ));
            });
    }

    /**
     * @return Collection<int, Negocio>
     */
    public function negociosQueVencenEnDias(int $dias): Collection
    {
        return Negocio::query()
            ->where('estado_suscripcion', EstadoSuscripcion::Activo)
            ->whereDate('proxima_fecha_pago', now()->addDays($dias)->toDateString())
            ->get();
    }

    /**
     * @return Collection<int, Negocio>
     */
    public function negociosVencidos(): Collection
    {
        return Negocio::query()->where('estado_suscripcion', EstadoSuscripcion::Vencido)->get();
    }

    private function calcularSiguienteEstadoAutomatico(Negocio $negocio): ?EstadoSuscripcion
    {
        $hoy = Carbon::now();

        return match ($negocio->estado_suscripcion) {
            EstadoSuscripcion::Activo => $this->yaVencioLaFechaDePago($negocio, $hoy)
                ? EstadoSuscripcion::Vencido
                : null,
            EstadoSuscripcion::Vencido => $this->yaPasoLaGracia($negocio, $hoy)
                ? EstadoSuscripcion::Suspendido
                : null,
            EstadoSuscripcion::Suspendido => $this->yaPasoElPlazoParaCancelar($negocio, $hoy)
                ? EstadoSuscripcion::Cancelado
                : null,
            EstadoSuscripcion::Cancelado => null,
        };
    }

    private function yaVencioLaFechaDePago(Negocio $negocio, Carbon $hoy): bool
    {
        return $negocio->proxima_fecha_pago !== null
            && $hoy->toDateString() >= $negocio->proxima_fecha_pago->toDateString();
    }

    private function yaPasoLaGracia(Negocio $negocio, Carbon $hoy): bool
    {
        return $negocio->vencido_desde !== null
            && $hoy->greaterThanOrEqualTo($negocio->vencido_desde->copy()->addDays($negocio->dias_gracia));
    }

    private function yaPasoElPlazoParaCancelar(Negocio $negocio, Carbon $hoy): bool
    {
        return $negocio->suspendido_desde !== null
            && $hoy->greaterThanOrEqualTo($negocio->suspendido_desde->copy()->addMonths($negocio->meses_para_cancelar));
    }
}
