<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EstadoCitaValor;
use App\Enums\MetodoPago;
use App\Events\PagoRegistrado;
use App\Exceptions\CitaNoCompletadaException;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Usuario;

/**
 * CU-08: el profesional o recepción registra el pago al completar la
 * cita. Solo procede sobre una cita ya completada (sección 4.8, F3.3).
 */
final class RegistrarPagoDeCita
{
    public function ejecutar(Cita $cita, MetodoPago $metodo, int $valor, int $propina = 0, ?Usuario $registradoPor = null): Pago
    {
        if ($cita->estado !== EstadoCitaValor::Completada) {
            throw new CitaNoCompletadaException;
        }

        $pago = Pago::create([
            'negocio_id' => $cita->negocio_id,
            'cita_id' => $cita->id,
            'metodo' => $metodo,
            'valor' => $valor,
            'propina' => $propina,
            'registrado_por_id' => $registradoPor?->id,
        ]);

        PagoRegistrado::dispatch($cita, $pago, $registradoPor);

        return $pago;
    }
}
