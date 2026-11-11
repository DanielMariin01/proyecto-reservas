<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\CambiarVisibilidadResena;
use App\Actions\ResponderResena;
use App\Enums\EstadoResenaValor;
use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudResponderResena;
use App\Http\Resources\App\RecursoResenaNegocio;
use App\Models\Negocio;
use App\Models\Resena;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

final class ResenaController extends Controller
{
    /**
     * El parametro Negocio $negocio no se usa directamente en varios de
     * estos metodos, pero es necesario: Laravel solo resuelve el binding
     * implicito de {negocio:slug} cuando el metodo del controlador lo
     * tipa, y ResolverNegocioDesdeRuta depende de que ese binding ya
     * este resuelto para fijar el contexto del negocio actual.
     */
    public function index(Request $request, Negocio $negocio): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Resena::class);

        $usuario = $this->usuarioActual();
        $profesional = $usuario->profesional;
        $profesionalId = $profesional === null ? 0 : $profesional->id;

        $resenas = Resena::query()
            ->with(['cliente', 'profesional'])
            ->when(
                ! $usuario->es_super_admin && ! $usuario->can(Permiso::GestionarResenas->value),
                fn (Builder $query): Builder => $query->where('profesional_id', $profesionalId),
            )
            ->when(
                $request->filled('estado'),
                fn (Builder $query): Builder => $query->where('estado', $request->string('estado')),
            )
            ->when(
                $request->filled('calificacion'),
                fn (Builder $query): Builder => $query->where('calificacion', $request->integer('calificacion')),
            )
            ->orderByDesc('created_at')
            ->paginate(25);

        return RecursoResenaNegocio::collection($resenas);
    }

    public function responder(SolicitudResponderResena $solicitud, Negocio $negocio, int $resena): JsonResponse
    {
        $resenaModelo = Resena::query()->findOrFail($resena);
        $this->authorize('update', $resenaModelo);

        app(ResponderResena::class)->ejecutar($resenaModelo, $solicitud->validated()['respuesta'], $this->usuarioActual());

        return response()->json(['data' => new RecursoResenaNegocio($resenaModelo->refresh())]);
    }

    public function ocultar(Negocio $negocio, int $resena): JsonResponse
    {
        $resenaModelo = Resena::query()->findOrFail($resena);
        $this->authorize('update', $resenaModelo);

        app(CambiarVisibilidadResena::class)->ejecutar($resenaModelo, EstadoResenaValor::Oculta, $this->usuarioActual());

        return response()->json(['data' => new RecursoResenaNegocio($resenaModelo->refresh())]);
    }

    public function mostrar(Negocio $negocio, int $resena): JsonResponse
    {
        $resenaModelo = Resena::query()->findOrFail($resena);
        $this->authorize('update', $resenaModelo);

        app(CambiarVisibilidadResena::class)->ejecutar($resenaModelo, EstadoResenaValor::Publicada, $this->usuarioActual());

        return response()->json(['data' => new RecursoResenaNegocio($resenaModelo->refresh())]);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
