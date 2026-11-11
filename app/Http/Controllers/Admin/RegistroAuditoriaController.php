<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RecursoRegistroAuditoria;
use App\Models\RegistroAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class RegistroAuditoriaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $registros = RegistroAuditoria::query()
            ->with(['negocio', 'usuario'])
            ->when(
                $request->filled('buscar'),
                fn (Builder $query): Builder => $query->where('accion', 'like', '%'.$request->string('buscar').'%'),
            )
            ->when(
                $request->filled('negocioId'),
                fn (Builder $query): Builder => $query->where('negocio_id', $request->integer('negocioId')),
            )
            ->orderByDesc('created_at')
            ->paginate(25);

        return RecursoRegistroAuditoria::collection($registros);
    }
}
