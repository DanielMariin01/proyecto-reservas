<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\App\RecursoCliente;
use App\Models\Cliente;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

final class ClienteController extends Controller
{
    /**
     * El parametro Negocio $negocio no se usa directamente, pero es
     * necesario: Laravel solo resuelve el binding implicito de
     * {negocio:slug} cuando el metodo del controlador lo tipa, y
     * ResolverNegocioDesdeRuta depende de que ese binding ya este
     * resuelto para fijar el contexto del negocio actual.
     */
    public function index(Request $request, Negocio $negocio): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Cliente::class);

        $buscar = $request->string('buscar')->trim()->toString();

        $clientes = Cliente::query()
            ->withCount('citas')
            ->when($buscar !== '', function ($consulta) use ($buscar): void {
                $consulta->where(function ($consulta) use ($buscar): void {
                    $consulta->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('celular', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(20);

        return RecursoCliente::collection($clientes);
    }

    public function show(Negocio $negocio, int $cliente): JsonResource
    {
        $this->authorize('viewAny', Cliente::class);

        $clienteModelo = Cliente::query()->withCount('citas')->findOrFail($cliente);

        return new RecursoCliente($clienteModelo);
    }
}
