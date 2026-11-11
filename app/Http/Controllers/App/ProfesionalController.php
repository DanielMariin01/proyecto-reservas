<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\CrearProfesional;
use App\Actions\VincularUsuarioAProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudActualizarProfesional;
use App\Http\Requests\App\SolicitudCrearProfesional;
use App\Http\Requests\App\SolicitudVincularUsuario;
use App\Http\Resources\App\RecursoProfesional;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class ProfesionalController extends Controller
{
    private const PESO_MAXIMO_IMAGEN_KB = 5120;

    /**
     * El parametro Negocio $negocio no se usa directamente en varios de
     * estos metodos, pero es necesario: Laravel solo resuelve el binding
     * implicito de {negocio:slug} cuando el metodo del controlador lo
     * tipa, y ResolverNegocioDesdeRuta depende de que ese binding ya
     * este resuelto para fijar el contexto del negocio actual.
     */
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Profesional::class);

        $profesionales = Profesional::query()->with('usuario')->orderBy('nombre')->get();

        return RecursoProfesional::collection($profesionales);
    }

    public function store(SolicitudCrearProfesional $solicitud, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Profesional::class);

        $datos = $solicitud->validated();

        $profesional = app(CrearProfesional::class)->ejecutar(
            new DatosNuevoProfesional(
                negocio: $negocio,
                nombre: $datos['nombre'],
                foto: $datos['foto'] ?? null,
                especialidad: $datos['especialidad'] ?? null,
                biografia: $datos['biografia'] ?? null,
                certificaciones: $datos['certificaciones'] ?? null,
                color: $datos['color'] ?? null,
                nombreUsuario: $datos['nombre_usuario'] ?? null,
                emailUsuario: $datos['email_usuario'] ?? null,
            ),
            $this->usuarioActual(),
        );

        return response()->json(['data' => new RecursoProfesional($profesional->load('usuario'))], 201);
    }

    public function update(SolicitudActualizarProfesional $solicitud, Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        $profesionalModelo->update($solicitud->validated());

        return response()->json(['data' => new RecursoProfesional($profesionalModelo->load('usuario'))]);
    }

    public function destroy(Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('delete', $profesionalModelo);

        $profesionalModelo->delete();

        return response()->json(['mensaje' => 'Profesional eliminado.']);
    }

    public function vincularUsuario(SolicitudVincularUsuario $solicitud, Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        $datos = $solicitud->validated();

        app(VincularUsuarioAProfesional::class)->ejecutar(
            $profesionalModelo,
            $datos['nombre'],
            $datos['email'],
            $this->usuarioActual(),
        );

        return response()->json(['data' => new RecursoProfesional($profesionalModelo->refresh()->load('usuario'))]);
    }

    public function subirImagen(Request $request): JsonResponse
    {
        $this->authorize('create', Profesional::class);

        $request->validate([
            'imagen' => ['required', 'image', 'max:'.self::PESO_MAXIMO_IMAGEN_KB],
        ]);

        $ruta = $request->file('imagen')->store('profesionales', 'public');

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
