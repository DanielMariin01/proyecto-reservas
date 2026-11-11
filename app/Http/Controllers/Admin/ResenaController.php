<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\CambiarVisibilidadResena;
use App\Enums\EstadoResenaValor;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RecursoResena;
use App\Models\Resena;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

final class ResenaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $resenas = Resena::query()
            ->with(['negocio', 'cliente', 'profesional'])
            ->when(
                $request->filled('negocioId'),
                fn (Builder $query): Builder => $query->where('negocio_id', $request->integer('negocioId')),
            )
            ->when(
                $request->filled('estado'),
                fn (Builder $query): Builder => $query->where('estado', $request->string('estado')),
            )
            ->orderByDesc('created_at')
            ->paginate(25);

        return RecursoResena::collection($resenas);
    }

    public function ocultar(Resena $resena): JsonResponse
    {
        app(CambiarVisibilidadResena::class)->ejecutar($resena, EstadoResenaValor::Oculta, $this->usuarioActual());

        return response()->json(['data' => new RecursoResena($resena)]);
    }

    public function mostrar(Resena $resena): JsonResponse
    {
        app(CambiarVisibilidadResena::class)->ejecutar($resena, EstadoResenaValor::Publicada, $this->usuarioActual());

        return response()->json(['data' => new RecursoResena($resena)]);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
