<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RecursoRegistroError;
use App\Models\RegistroError;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class RegistroErrorController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $registros = RegistroError::query()
            ->with(['negocio', 'usuario'])
            ->when(
                $request->filled('negocioId'),
                fn (Builder $query): Builder => $query->where('negocio_id', $request->integer('negocioId')),
            )
            ->when(
                $request->filled('buscar'),
                fn (Builder $query): Builder => $query->where('clase_excepcion', 'like', '%'.$request->string('buscar').'%'),
            )
            ->when(
                $request->boolean('ultimas24h', true),
                fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDay()),
            )
            ->orderByDesc('created_at')
            ->paginate(25);

        return RecursoRegistroError::collection($registros);
    }
}
