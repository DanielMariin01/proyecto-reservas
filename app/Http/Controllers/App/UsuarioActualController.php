<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Datos del usuario autenticado dentro del panel app, para decisiones de
 * interfaz que no justifican una llamada propia: si el menú debe mostrar
 * "Mi horario" y "Mis citas de hoy" (exclusivas de quien tiene un
 * Profesional vinculado), si debe mostrar "Finanzas" (quien puede ver
 * reportes, ver PoliticaGasto::viewAny), "Productos" (PoliticaProducto::viewAny),
 * "Pedidos" (PoliticaPedido::viewAny) y "Clientes"/"Historial clínico"
 * (PoliticaCliente::viewAny y PoliticaHistoriaClinica::viewAny, esta última
 * ya incluye el filtro de RF-HCL-01 por tipo de negocio).
 */
final class UsuarioActualController extends Controller
{
    /**
     * El parametro Negocio $negocio no se usa directamente, pero es
     * necesario: Laravel solo resuelve el binding implicito de
     * {negocio:slug} cuando el metodo del controlador lo tipa, y
     * ResolverNegocioDesdeRuta depende de que ese binding ya este
     * resuelto para fijar el contexto del negocio actual.
     */
    public function mostrar(Negocio $negocio): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return response()->json([
            'esProfesional' => $usuario->profesional !== null,
            'puedeVerFinanzas' => $usuario->can('viewAny', Gasto::class),
            'puedeGestionarProductos' => $usuario->can('viewAny', Producto::class),
            'puedeGestionarPedidos' => $usuario->can('viewAny', Pedido::class),
            'puedeVerClientes' => $usuario->can('viewAny', Cliente::class),
            'puedeGestionarHistoriasClinicas' => $usuario->can('viewAny', HistoriaClinica::class),
        ]);
    }
}
