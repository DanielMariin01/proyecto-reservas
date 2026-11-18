<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cliente;

use App\Enums\EstadoCitaValor;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\CuentaCliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * RF-HCL-08-c: el panel de tarjetas de /mi-cuenta. Una sola consulta
 * agrupada por estado, igual de barata que PanelDelDiaController, en vez
 * de una consulta por tarjeta.
 */
final class EstadisticasController extends Controller
{
    public function mostrar(): JsonResponse
    {
        $cuenta = $this->cuentaActual();

        $porEstado = Cita::query()
            ->whereHas('cliente', fn ($consulta) => $consulta->where('cuenta_cliente_id', $cuenta->id))
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $pendientesOProximas = [EstadoCitaValor::Pendiente, EstadoCitaValor::Confirmada, EstadoCitaValor::EnAtencion];

        return response()->json([
            'citasTotales' => (int) $porEstado->sum(),
            'citasPendientes' => (int) collect($pendientesOProximas)->sum(fn (EstadoCitaValor $estado) => $porEstado[$estado->value] ?? 0),
            'citasCompletadas' => (int) ($porEstado[EstadoCitaValor::Completada->value] ?? 0),
            'citasCanceladas' => (int) ($porEstado[EstadoCitaValor::Cancelada->value] ?? 0),
            'citasNoAsistio' => (int) ($porEstado[EstadoCitaValor::NoAsistio->value] ?? 0),
        ]);
    }

    private function cuentaActual(): CuentaCliente
    {
        /** @var CuentaCliente $cuenta */
        $cuenta = Auth::guard('cliente')->user();

        return $cuenta;
    }
}
