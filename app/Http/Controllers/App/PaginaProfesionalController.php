<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Profesional;
use Illuminate\Contracts\View\View;

final class PaginaProfesionalController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', Profesional::class);

        return view('panel-negocio.profesionales', ['negocio' => $negocio]);
    }

    public function create(Negocio $negocio): View
    {
        $this->authorize('create', Profesional::class);

        return view('panel-negocio.profesionales-crear', ['negocio' => $negocio]);
    }

    public function show(Negocio $negocio, int $profesional): View
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('view', $profesionalModelo);

        return view('panel-negocio.profesionales-ver', ['negocio' => $negocio, 'profesional' => $profesionalModelo]);
    }

    public function edit(Negocio $negocio, int $profesional): View
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        return view('panel-negocio.profesionales-editar', ['negocio' => $negocio, 'profesional' => $profesionalModelo]);
    }
}
