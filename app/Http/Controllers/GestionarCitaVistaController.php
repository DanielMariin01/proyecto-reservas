<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class GestionarCitaVistaController extends Controller
{
    public function mostrar(Negocio $negocio, string $token): View
    {
        return view('gestionar-cita', ['negocio' => $negocio, 'token' => $token]);
    }
}
