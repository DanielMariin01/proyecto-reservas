<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Resources\Publico\RecursoPaginaPublica;
use App\Models\Negocio;
use App\Models\Pagina;
use Illuminate\Http\Request;

final class PaginaController extends Controller
{
    /**
     * RF-BLQ-01: siempre sirve bloques_publicados. La única excepción es
     * RF-PAG-06 (vista previa): con ?vista_previa=1 y una sesión de panel
     * autorizada para este negocio, sirve el borrador en su lugar.
     */
    public function mostrar(Request $request, Negocio $negocio): RecursoPaginaPublica
    {
        $pagina = Pagina::query()->where('negocio_id', $negocio->id)->first();

        $mostrarBorrador = $request->boolean('vista_previa') && $this->puedeVerBorrador($request, $negocio);

        return new RecursoPaginaPublica($negocio, $pagina, $mostrarBorrador);
    }

    private function puedeVerBorrador(Request $request, Negocio $negocio): bool
    {
        $usuario = $request->user('web');

        if ($usuario === null) {
            return false;
        }

        if ($usuario->es_super_admin) {
            return true;
        }

        return $negocio->permite_editar_pagina && $negocio->duenos()->contains(fn ($dueno) => $dueno->id === $usuario->id);
    }
}
