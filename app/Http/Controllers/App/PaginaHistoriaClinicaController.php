<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class PaginaHistoriaClinicaController extends Controller
{
    public function create(Negocio $negocio, int $cliente): View
    {
        $this->authorize('create', HistoriaClinica::class);

        $clienteModelo = Cliente::query()->findOrFail($cliente);

        return view('panel-negocio.historias-clinicas-crear', ['negocio' => $negocio, 'cliente' => $clienteModelo]);
    }

    public function edit(Negocio $negocio, int $cliente, int $historiaClinica): View
    {
        $historiaClinicaModelo = HistoriaClinica::query()->findOrFail($historiaClinica);
        $this->authorize('update', $historiaClinicaModelo);

        $clienteModelo = Cliente::query()->findOrFail($cliente);

        return view('panel-negocio.historias-clinicas-editar', [
            'negocio' => $negocio,
            'cliente' => $clienteModelo,
            'historiaClinica' => $historiaClinicaModelo->id,
        ]);
    }
}
