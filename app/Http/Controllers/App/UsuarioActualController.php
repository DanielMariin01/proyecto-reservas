<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Datos del usuario autenticado dentro del panel app, para decisiones de
 * interfaz que no justifican una llamada propia: si el menú debe mostrar
 * "Mi horario" y "Mis citas de hoy" (exclusivas de quien tiene un
 * Profesional vinculado), si debe mostrar "Finanzas" (quien puede ver
 * reportes, ver PoliticaGasto::viewAny), "Productos" (PoliticaProducto::viewAny),
 * "Pedidos" (PoliticaPedido::viewAny), "Clientes"/"Historial clínico"
 * (PoliticaCliente::viewAny y PoliticaHistoriaClinica::viewAny, esta última
 * ya incluye el filtro de RF-HCL-01 por tipo de negocio), "Agenda"
 * (GestionarCitasTodas, distinto de "Citas" que también dejan ver
 * GestionarCitasPropias), "Profesionales"/"Servicios"/"Categorías de
 * servicio" (PoliticaServicio::viewAny, el mismo helper que ya comparten
 * las 3), "Página web" (igual chequeo que PaginaPaginaWebController, para
 * no ofrecer el enlace si permite_editar_pagina está apagado) y
 * "Reseñas"/"Usuarios". El menú (LayoutPanelNegocio) oculta cada enlace
 * que de por sí llevaría a un 403, en vez de mostrarlo y dejar que la
 * pantalla reviente.
 */
final class UsuarioActualController extends Controller
{
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
            'puedeGestionarCitasTodas' => $usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value),
            'puedeGestionarCatalogo' => $usuario->can('viewAny', Servicio::class),
            'puedeGestionarNegocio' => $usuario->es_super_admin
                || ($usuario->can(Permiso::GestionarNegocio->value) && $negocio->permite_editar_pagina),
            'puedeGestionarResenas' => $usuario->can('viewAny', Resena::class),
            'puedeGestionarUsuarios' => $usuario->can('viewAny', Usuario::class),
        ]);
    }
}
