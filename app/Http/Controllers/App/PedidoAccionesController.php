<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\CambiarEstadoPedido;
use App\Actions\RegistrarPagoDePedido;
use App\Enums\EstadoPedidoValor;
use App\Enums\MetodoPago;
use App\Exceptions\PedidoNoPagadoException;
use App\Exceptions\TransicionDeEstadoPedidoInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudCambiarEstadoPedido;
use App\Http\Requests\App\SolicitudMarcarPedidoPagado;
use App\Http\Resources\App\RecursoPedido;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Las 2 acciones sobre un pedido puntual (cambiar estado, marcar
 * pagado), igual patron que CitaAccionesController pero mas simple:
 * un pedido no tiene "propio profesional" ni reprogramacion.
 */
final class PedidoAccionesController extends Controller
{
    public function cambiarEstado(SolicitudCambiarEstadoPedido $solicitud, Negocio $negocio, int $pedido): JsonResponse
    {
        $pedidoModelo = Pedido::query()->findOrFail($pedido);
        $this->authorize('update', $pedidoModelo);

        try {
            app(CambiarEstadoPedido::class)->ejecutar(
                $pedidoModelo,
                EstadoPedidoValor::from($solicitud->validated()['estado']),
                $this->usuarioActual(),
            );
        } catch (TransicionDeEstadoPedidoInvalidaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoPedido($pedidoModelo->refresh()->load(['cliente', 'items']))]);
    }

    public function marcarPagado(SolicitudMarcarPedidoPagado $solicitud, Negocio $negocio, int $pedido): JsonResponse
    {
        $pedidoModelo = Pedido::query()->findOrFail($pedido);
        $this->authorize('update', $pedidoModelo);

        $datos = $solicitud->validated();

        try {
            $usuario = $this->usuarioActual();
            app(CambiarEstadoPedido::class)->ejecutar($pedidoModelo, EstadoPedidoValor::Pagado, $usuario);
            app(RegistrarPagoDePedido::class)->ejecutar(
                pedido: $pedidoModelo,
                metodo: MetodoPago::from($datos['metodo']),
                valor: (int) $datos['valor'],
                registradoPor: $usuario,
            );
        } catch (TransicionDeEstadoPedidoInvalidaException|PedidoNoPagadoException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoPedido($pedidoModelo->refresh()->load(['cliente', 'items']))]);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
