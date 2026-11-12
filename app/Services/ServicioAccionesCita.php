<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoCitaValor;
use App\Enums\Permiso;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Usuario;

/**
 * Qué puede hacer un usuario con una cita puntual (completar con pago,
 * cambiar estado, reprogramar, cancelar, anular pago): la misma
 * combinación de reglas de estado (patrón Estado, `EstadoCita`) y
 * políticas que hoy decide qué botones mostrar en Filament
 * (`AccionesDeCita`), pero independiente de Filament para que la
 * reutilicen tanto la Agenda del día (React) como, más adelante, la
 * migración de `CitaResource`.
 */
final class ServicioAccionesCita
{
    public function puedeCompletarConPago(Cita $cita, Usuario $usuario): bool
    {
        return $usuario->can('update', $cita)
            && $cita->obtenerEstado()->puedeTransicionarA(EstadoCitaValor::Completada)
            && $usuario->can('create', [Pago::class, $cita]);
    }

    public function puedeCambiarEstado(Cita $cita, Usuario $usuario): bool
    {
        return $usuario->can('update', $cita) && $this->destinosPosiblesDeCambioDeEstado($cita) !== [];
    }

    /**
     * Reprogramada se maneja con su propia acción; Completada exige pasar
     * por completarConPago() para no saltarse el registro del pago.
     *
     * @return list<EstadoCitaValor>
     */
    public function destinosPosiblesDeCambioDeEstado(Cita $cita): array
    {
        $estado = $cita->obtenerEstado();
        $destinos = [];

        foreach (EstadoCitaValor::cases() as $destino) {
            if ($destino === EstadoCitaValor::Reprogramada || $destino === EstadoCitaValor::Completada) {
                continue;
            }

            if ($estado->puedeTransicionarA($destino)) {
                $destinos[] = $destino;
            }
        }

        return $destinos;
    }

    public function puedeReprogramar(Cita $cita, Usuario $usuario): bool
    {
        return $usuario->can('update', $cita) && $cita->obtenerEstado()->puedeTransicionarA(EstadoCitaValor::Reprogramada);
    }

    public function puedeCancelar(Cita $cita, Usuario $usuario): bool
    {
        return $usuario->can('update', $cita) && ! $cita->obtenerEstado()->esFinal();
    }

    public function puedeAnularPago(Cita $cita, Usuario $usuario): bool
    {
        $pago = $cita->pagoActivo();

        return $pago instanceof Pago && $usuario->can('anular', $pago);
    }

    public function puedeForzarReprogramacion(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value);
    }
}
