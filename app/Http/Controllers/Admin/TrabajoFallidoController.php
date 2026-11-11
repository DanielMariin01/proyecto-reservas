<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RecursoTrabajoFallido;
use App\Models\TrabajoFallido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Artisan;

final class TrabajoFallidoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $trabajos = TrabajoFallido::query()->orderByDesc('failed_at')->paginate(25);

        return RecursoTrabajoFallido::collection($trabajos);
    }

    public function reintentar(TrabajoFallido $trabajo): JsonResponse
    {
        Artisan::call('queue:retry', ['id' => [$trabajo->uuid]]);

        return response()->json(['mensaje' => 'El trabajo vuelve a la cola.']);
    }
}
