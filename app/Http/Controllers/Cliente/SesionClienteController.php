<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cliente\RecursoCuentaCliente;
use App\Models\CuentaCliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class SesionClienteController extends Controller
{
    public function mostrar(): JsonResponse
    {
        /** @var CuentaCliente|null $cuenta */
        $cuenta = Auth::guard('cliente')->user();

        return response()->json(['data' => $cuenta === null ? null : new RecursoCuentaCliente($cuenta)]);
    }

    public function salir(Request $request): JsonResponse
    {
        Auth::guard('cliente')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['data' => null]);
    }
}
