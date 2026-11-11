<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RecursoPagoSuscripcion;
use App\Models\Negocio;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PagoSuscripcionController extends Controller
{
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $pagos = $negocio->pagosSuscripcion()
            ->with('registradoPor')
            ->orderByDesc('fecha')
            ->paginate(25);

        return RecursoPagoSuscripcion::collection($pagos);
    }
}
