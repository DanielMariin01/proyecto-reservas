<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\ActualizarBloquesPagina;
use App\Actions\PublicarPagina;
use App\Enums\TipoBloque;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SolicitudActualizarBloquesPagina;
use App\Http\Resources\Admin\RecursoPaginaWeb;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

final class PaginaWebController extends Controller
{
    private const PESO_MAXIMO_IMAGEN_KB = 5120;

    /**
     * `portada` se muestra a pantalla completa (hasta 21:9 en escritorio
     * grande, sección 6.1 de la skill de diseño) con `object-cover`: una
     * foto de baja resolución se ve pixelada porque el navegador la
     * estira para llenar ese espacio. Sin librería de imágenes en el
     * proyecto (hosting compartido) no hay forma de corregirla después
     * de subida, así que se exige la resolución mínima en el origen.
     */
    private const ANCHO_MINIMO_PORTADA = 1600;

    private const ALTO_MINIMO_PORTADA = 900;

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
        $reglasImagen = ['required', 'image', 'max:'.self::PESO_MAXIMO_IMAGEN_KB];

        if ($request->string('tipo_bloque')->toString() === TipoBloque::Portada->value) {
            $reglasImagen[] = Rule::dimensions()->minWidth(self::ANCHO_MINIMO_PORTADA)->minHeight(self::ALTO_MINIMO_PORTADA);
        }

        $request->validate(['imagen' => $reglasImagen], [
            'imagen.dimensions' => 'La foto debe tener al menos 1600×900 píxeles para que no se vea pixelada en pantallas grandes.',
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
