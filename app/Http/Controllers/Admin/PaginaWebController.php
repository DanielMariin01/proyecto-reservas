<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\ActualizarBloquesPagina;
use App\Actions\PublicarPagina;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SolicitudActualizarBloquesPagina;
use App\Http\Resources\Admin\RecursoPaginaWeb;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class PaginaWebController extends Controller
{
    private const PESO_MAXIMO_IMAGEN_KB = 5120;

    public function show(Negocio $negocio): JsonResponse
    {
        $pagina = $negocio->pagina;

        if ($pagina === null) {
            return response()->json(['data' => [
                'bloques' => [],
                'estaPublicada' => false,
                'publicadaEn' => null,
                'hayCambiosSinPublicar' => false,
            ]]);
        }

        return response()->json(['data' => new RecursoPaginaWeb($pagina)]);
    }

    public function update(SolicitudActualizarBloquesPagina $solicitud, Negocio $negocio): JsonResponse
    {
        $pagina = app(ActualizarBloquesPagina::class)->ejecutar($negocio, $solicitud->validated()['bloques']);

        return response()->json(['data' => new RecursoPaginaWeb($pagina)]);
    }

    public function publicar(Negocio $negocio): JsonResponse
    {
        $pagina = $negocio->pagina;

        if ($pagina === null) {
            return response()->json(['mensaje' => 'Guarda la página antes de publicarla.'], 422);
        }

        app(PublicarPagina::class)->ejecutar($pagina, $this->usuarioActual());

        return response()->json(['data' => new RecursoPaginaWeb($pagina->refresh())]);
    }

    public function subirImagen(Request $request): JsonResponse
    {
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

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
