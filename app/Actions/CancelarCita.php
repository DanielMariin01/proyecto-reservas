<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EstadoCitaValor;
use App\Enums\Permiso;
use App\Events\CitaCancelada;
use App\Exceptions\FueraDePoliticaDeCancelacionException;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Usuario;
use Carbon\CarbonImmutable;

final class CancelarCita
{
    public function ejecutar(Cita $cita, string $motivo, ?Usuario $canceladoPor = null): Cita
    {
        $estado = $cita->obtenerEstado();

        if (! $estado->puedeTransicionarA(EstadoCitaValor::Cancelada)) {
            throw new TransicionDeEstadoInvalidaException($estado->valor(), EstadoCitaValor::Cancelada);
        }

        if ($this->debeRespetarPolitica($canceladoPor)) {
            $this->validarVentana($cita);
        }

        $cita->update([
            'estado' => EstadoCitaValor::Cancelada,
            'motivo_cancelacion' => $motivo,
        ]);

        CitaCancelada::dispatch($cita, $canceladoPor);

        return $cita;
    }

    private function debeRespetarPolitica(?Usuario $canceladoPor): bool
    {
        if ($canceladoPor === null) {
            return true;
        }

        return ! $canceladoPor->can(Permiso::GestionarCitasTodas->value);
    }

    private function validarVentana(Cita $cita): void
    {
        $negocio = Negocio::findOrFail($cita->negocio_id);
        $limite = $cita->inicio->subHours($negocio->horas_minimas_cancelacion);

        if (CarbonImmutable::now()->greaterThan($limite)) {
            throw new FueraDePoliticaDeCancelacionException($negocio->horas_minimas_cancelacion);
        }
    }
}
