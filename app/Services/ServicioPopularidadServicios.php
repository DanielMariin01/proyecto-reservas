<?php

declare(strict_types=1);

namespace App\Services;

use App\DatosTransferencia\DatosPopularidadServicio;
use App\Enums\EstadoCitaValor;
use App\Models\Negocio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ranking real para el bloque `servicios_destacados`: cuenta citas
 * `completada` por servicio, no un orden manual. Ventana de 90 días para
 * que el carrusel refleje lo que se reserva ahora; si el negocio no tuvo
 * ninguna cita completada en ese periodo (negocio nuevo o muy tranquilo),
 * cae al histórico completo con el mismo criterio de estado, para no
 * dejar el bloque vacío.
 */
final class ServicioPopularidadServicios
{
    private const DIAS_VENTANA = 90;

    /**
     * @return Collection<int, DatosPopularidadServicio>
     */
    public function topServicios(Negocio $negocio, int $limite): Collection
    {
        $conteos = $this->conteosPorServicio($negocio, CarbonImmutable::now()->subDays(self::DIAS_VENTANA));

        if ($conteos->isEmpty()) {
            $conteos = $this->conteosPorServicio($negocio, null);
        }

        return $conteos
            ->take($limite)
            ->values()
            ->map(fn (\stdClass $fila, int $indice): DatosPopularidadServicio => new DatosPopularidadServicio(
                servicioId: (int) $fila->servicio_id,
                puesto: $indice + 1,
                totalReservas: (int) $fila->total_reservas,
                clientesDistintos: (int) $fila->clientes_distintos,
            ));
    }

    /**
     * Consulta por `DB::table` (no `Cita::query()`): las columnas agregadas
     * (`total_reservas`, `clientes_distintos`) no existen en el modelo, así
     * que se leen como filas `stdClass` en vez de forzar propiedades que
     * Larastan no puede conocer de antemano.
     *
     * @return Collection<int, \stdClass>
     */
    private function conteosPorServicio(Negocio $negocio, ?CarbonImmutable $desde): Collection
    {
        $consulta = DB::table('citas')
            ->where('negocio_id', $negocio->id)
            ->where('estado', EstadoCitaValor::Completada->value);

        if ($desde !== null) {
            $consulta->where('inicio', '>=', $desde);
        }

        return $consulta
            ->selectRaw('servicio_id, count(*) as total_reservas, count(distinct cliente_id) as clientes_distintos')
            ->groupBy('servicio_id')
            ->orderByDesc('total_reservas')
            ->get();
    }
}
