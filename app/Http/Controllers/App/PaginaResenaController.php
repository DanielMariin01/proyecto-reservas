<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Resena;
use Illuminate\Contracts\View\View;

final class PaginaResenaController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', Resena::class);

        return view('panel-negocio.resenas', ['negocio' => $negocio]);
    }
}
