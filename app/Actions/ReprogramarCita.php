<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosReprogramacion;
use App\Disponibilidad\HoraDisponible;
use App\Enums\EstadoCitaValor;
use App\Enums\Permiso;
use App\Events\CitaReprogramada;
use App\Exceptions\FueraDePoliticaDeCancelacionException;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\MaximoDeReprogramacionesException;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\ServicioDisponibilidad;
use App\Services\ServicioReservas;
use Carbon\CarbonImmutable;

final class ReprogramarCita
{
    public function __construct(
        private readonly ServicioReservas $servicioReservas,
        private readonly ServicioDisponibilidad $servicioDisponibilidad,
    ) {}

    public function ejecutar(DatosReprogramacion $datos): Cita
    {
        $cita = $datos->cita;
        $estado = $cita->obtenerEstado();

        if (! $estado->puedeTransicionarA(EstadoCitaValor::Reprogramada)) {
            throw new TransicionDeEstadoInvalidaException($estado->valor(), EstadoCitaValor::Reprogramada);
        }

        $negocio = Negocio::findOrFail($cita->negocio_id);

        if ($cita->veces_reprogramada >= $negocio->maximo_reprogramaciones) {
            throw new MaximoDeReprogramacionesException($negocio->maximo_reprogramaciones);
        }

        if ($this->debeRespetarPolitica($datos->reprogramadaPor)) {
            $this->validarVentana($cita, $negocio);
        }

        $servicio = Servicio::findOrFail($cita->servicio_id);

        if (! $datos->forzar && ! $this->esFranjaValida($cita, $servicio, $datos->nuevoInicio, $negocio)) {
            throw new HoraInvalidaException;
        }

        $inicioAnterior = $cita->inicio;

        $cita = $this->servicioReservas->moverA($cita, $datos->nuevoInicio, $servicio->duracion_minutos, $datos->forzar);

        $cita->update([
            'estado' => EstadoCitaValor::Confirmada,
            'veces_reprogramada' => $cita->veces_reprogramada + 1,
        ]);

        CitaReprogramada::dispatch($cita, $inicioAnterior, $datos->reprogramadaPor);

        return $cita;
    }

    private function debeRespetarPolitica(?Usuario $reprogramadaPor): bool
    {
        if ($reprogramadaPor === null) {
            return true;
        }

        return ! $reprogramadaPor->can(Permiso::GestionarCitasTodas->value);
    }

    private function validarVentana(Cita $cita, Negocio $negocio): void
    {
        $limite = $cita->inicio->subHours($negocio->horas_minimas_reprogramacion);

        if (CarbonImmutable::now()->greaterThan($limite)) {
            throw new FueraDePoliticaDeCancelacionException($negocio->horas_minimas_reprogramacion);
        }
    }

    private function esFranjaValida(Cita $cita, Servicio $servicio, CarbonImmutable $nuevoInicio, Negocio $negocio): bool
    {
        $profesional = Profesional::findOrFail($cita->profesional_id);
        $fechaLocal = CarbonImmutable::parse(
            $nuevoInicio->setTimezone($negocio->zona_horaria)->toDateString(),
            $negocio->zona_horaria,
        );

        return $this->servicioDisponibilidad
            ->obtenerFranjasDisponibles($profesional, $servicio, $fechaLocal)
            ->contains(fn (HoraDisponible $hora): bool => $hora->inicio->equalTo($nuevoInicio));
    }
}
