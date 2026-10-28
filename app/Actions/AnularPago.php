<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\PagoAnulado;
use App\Exceptions\PagoYaAnuladoException;
use App\Models\Pago;
use App\Models\Usuario;
use Carbon\CarbonImmutable;

/**
 * F5.2: el dueño corrige un pago mal registrado durante el cierre del día.
 * Nunca se borra: queda marcado como anulado, con motivo, para la
 * auditoría y para que no cuente en los ingresos del panel del día.
 */
final class AnularPago
{
    public function ejecutar(Pago $pago, string $motivo, ?Usuario $anuladoPor = null): Pago
    {
        if (! $pago->estaActivo()) {
            throw new PagoYaAnuladoException;
        }

        $pago->update([
            'anulado_en' => CarbonImmutable::now(),
            'motivo_anulacion' => $motivo,
        ]);

        PagoAnulado::dispatch($pago, $anuladoPor);

        return $pago;
    }
}
