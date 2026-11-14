<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Gasto;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Datos del usuario autenticado dentro del panel app, para decisiones de
 * interfaz que no justifican una llamada propia: si el menú debe mostrar
 * "Mi horario" y "Mis citas de hoy" (exclusivas de quien tiene un
 * Profesional vinculado), y si debe mostrar "Finanzas" (quien puede ver
 * reportes, ver PoliticaGasto::viewAny).
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
        ]);
    }
}
