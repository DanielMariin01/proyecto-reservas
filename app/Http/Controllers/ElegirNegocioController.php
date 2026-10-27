<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirigeANegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class ElegirNegocioController extends Controller
{
    use RedirigeANegocio;

    public function mostrar(): Response
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        $negocios = $usuario->negociosDondeTieneRol();

        return response()->view('auth.elegir-negocio', ['negocios' => $negocios]);
    }

    public function elegir(Negocio $negocio): Response
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        abort_unless($usuario->negociosDondeTieneRol()->contains('id', $negocio->id), 403);

        return $this->redirigirANegocio($negocio);
    }
}
