<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Servicio;
use Illuminate\Contracts\View\View;

final class PaginaServicioController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', Servicio::class);

        return view('panel-negocio.servicios', ['negocio' => $negocio]);
    }

    public function create(Negocio $negocio): View
    {
        $this->authorize('create', Servicio::class);

        return view('panel-negocio.servicios-crear', ['negocio' => $negocio]);
    }

    public function edit(Negocio $negocio, int $servicio): View
    {
        $servicioModelo = Servicio::query()->findOrFail($servicio);
        $this->authorize('update', $servicioModelo);

        return view('panel-negocio.servicios-editar', ['negocio' => $negocio, 'servicio' => $servicioModelo]);
    }
}
