<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\PublicarPagina;
use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudActualizarContenidoPagina;
use App\Models\Negocio;
use App\Models\Pagina;
use App\Models\Usuario;
use App\Services\ServicioContenidoEditableDePagina;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * RF-PAG-01: el dueño edita el texto y las fotos de los bloques que el
 * super admin ya dejó armados en el Builder (admin, T20), y publica
 * directo sus propios cambios sin pasar por el super admin.
 */
final class PaginaWebController extends Controller
{
    private const PESO_MAXIMO_IMAGEN_KB = 5120;

    public function __construct(private readonly ServicioContenidoEditableDePagina $servicioContenido) {}

    public function show(Negocio $negocio): JsonResponse
    {
        $this->autorizar($negocio);

        return response()->json(['bloques' => $this->servicioContenido->bloquesEditables($this->paginaOFallar($negocio)->bloques)]);
    }

    public function guardarYPublicar(SolicitudActualizarContenidoPagina $solicitud, Negocio $negocio): JsonResponse
    {
        $this->autorizar($negocio);

        $pagina = $this->servicioContenido->actualizar($this->paginaOFallar($negocio), $solicitud->validated()['bloques']);
        app(PublicarPagina::class)->ejecutar($pagina->refresh(), $this->usuarioActual());

        return response()->json(['bloques' => $this->servicioContenido->bloquesEditables($pagina->refresh()->bloques)]);
    }

    public function subirImagen(Request $request, Negocio $negocio): JsonResponse
    {
        $this->autorizar($negocio);

        $request->validate([
            'imagen' => ['required', 'image', 'max:'.self::PESO_MAXIMO_IMAGEN_KB],
        ]);

        $ruta = $request->file('imagen')->store('paginas', 'public');

        abort_if($ruta === false, 500, 'No se pudo guardar la imagen.');

        return response()->json([
            'ruta' => $ruta,
            'url' => Storage::disk('public')->url($ruta),
        ]);
    }

    private function paginaOFallar(Negocio $negocio): Pagina
    {
        $pagina = $negocio->pagina;

        abort_if($pagina === null, 404);

        return $pagina;
    }

    private function autorizar(Negocio $negocio): void
    {
        $usuario = $this->usuarioActual();

        if ($usuario->es_super_admin) {
            return;
        }

        abort_unless($usuario->can(Permiso::GestionarNegocio->value) && $negocio->permite_editar_pagina, 403);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
