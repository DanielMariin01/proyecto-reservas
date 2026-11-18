<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EstadoCitaValor;
use App\Enums\MetodoPago;
use App\Events\PagoRegistrado;
use App\Exceptions\CitaNoCompletadaException;
use App\Exceptions\MontoDePagoIncorrectoException;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Usuario;

/**
 * CU-08: el profesional o recepción registra el pago al completar la
 * cita. Solo procede sobre una cita ya completada (sección 4.8, F3.3).
 *
 * El monto pagado (sin contar la propina, que es aparte) debe coincidir
 * exactamente con el precio de la cita: no se permite marcar una cita
 * como pagada en su totalidad por menos de lo que vale, ni de más. Un
 * descuento se aplica bajando el precio del servicio o de la cita antes
 * de cobrar, no inflando o reduciendo el monto del pago.
 *
 * validar() se expone aparte de ejecutar() para que quien llama
 * (CitaAccionesController, MisCitasHoyController) pueda comprobar el
 * monto *antes* de pasar la cita a Completada: ese estado es terminal
 * (EstadoCompletada::puedeTransicionarA() siempre devuelve false), así
 * que si el rechazo llegara después de cambiar el estado, la cita
 * quedaría completada sin pago y sin ninguna forma de reintentarlo.
 */
final class RegistrarPagoDeCita
{
    public function validar(Cita $cita, int $valor): void
    {
        if ($valor !== $cita->precio) {
            throw new MontoDePagoIncorrectoException($cita->precio, $valor);
        }
    }

    public function ejecutar(Cita $cita, MetodoPago $metodo, int $valor, int $propina = 0, ?Usuario $registradoPor = null): Pago
    {
        if ($cita->estado !== EstadoCitaValor::Completada) {
            throw new CitaNoCompletadaException;
        }

        $this->validar($cita, $valor);

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
