<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cita;
use App\Models\Negocio;
use Carbon\CarbonImmutable;

/**
 * CU-02: lo que hace válido o no al enlace de cancelar/reprogramar del
 * correo. El token en sí (token_cancelacion) ya es el "firmado" del
 * RNF-04 — 40 caracteres aleatorios, imposible de adivinar, generado en
 * ServicioReservas (T5), sin tocar esa clase. El "vencimiento" es una
 * regla de este servicio: el enlace deja de servir cuando la cita ya no
 * está activa o cuando su hora de inicio ya pasó.
 */
final class ServicioEnlaceCita
{
    public function buscarPorToken(Negocio $negocio, string $token): Cita
    {
        return Cita::query()
            ->where('negocio_id', $negocio->id)
            ->where('token_cancelacion', $token)
            ->firstOrFail();
    }

    public function enlaceActivo(Cita $cita): bool
    {
        return $cita->estaActiva() && CarbonImmutable::now()->lessThan($cita->inicio);
    }

    public function puedeCancelar(Cita $cita): bool
    {
        if (! $this->enlaceActivo($cita)) {
            return false;
        }

        $negocio = Negocio::findOrFail($cita->negocio_id);

        return ! $this->fueraDeVentana($cita, $negocio->horas_minimas_cancelacion);
    }

    public function puedeReprogramar(Cita $cita): bool
    {
        if (! $this->enlaceActivo($cita)) {
            return false;
        }

        $negocio = Negocio::findOrFail($cita->negocio_id);

        if ($cita->veces_reprogramada >= $negocio->maximo_reprogramaciones) {
            return false;
        }

        return ! $this->fueraDeVentana($cita, $negocio->horas_minimas_reprogramacion);
    }

    private function fueraDeVentana(Cita $cita, int $horasMinimas): bool
    {
        return CarbonImmutable::now()->greaterThan($cita->inicio->subHours($horasMinimas));
    }
}
