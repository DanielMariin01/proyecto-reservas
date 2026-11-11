<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\CambiarEstadoNegocio;
use App\Actions\CrearNegocio;
use App\Actions\RegistrarPagoSuscripcion;
use App\Actions\SuplantarNegocio;
use App\DatosTransferencia\DatosCambioEstadoNegocio;
use App\DatosTransferencia\DatosNuevoNegocio;
use App\DatosTransferencia\DatosPagoSuscripcion;
use App\Enums\EstadoSuscripcion;
use App\Enums\TipoNegocio;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SolicitudActualizarNegocio;
use App\Http\Requests\Admin\SolicitudCambiarEstadoNegocio;
use App\Http\Requests\Admin\SolicitudCrearNegocio;
use App\Http\Requests\Admin\SolicitudRegistrarPagoSuscripcion;
use App\Http\Resources\Admin\RecursoNegocio;
use App\Http\Resources\Admin\RecursoNegocioDetalle;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

final class NegocioController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $negocios = Negocio::query()->orderBy('nombre')->paginate(25);

        return RecursoNegocio::collection($negocios);
    }

    public function show(Negocio $negocio): JsonResponse
    {
        return response()->json(['data' => new RecursoNegocioDetalle($negocio)]);
    }

    public function store(SolicitudCrearNegocio $solicitud): JsonResponse
    {
        $datos = $solicitud->validated();

        $negocio = app(CrearNegocio::class)->ejecutar(
            new DatosNuevoNegocio(
                nombre: $datos['nombre'],
                tipo: TipoNegocio::from($datos['tipo']),
                nombreDueno: $datos['nombre_dueno'],
                emailDueno: $datos['email_dueno'],
                slug: $datos['slug'] ?? null,
                direccion: $datos['direccion'] ?? null,
                ciudad: $datos['ciudad'] ?? null,
                telefono: $datos['telefono'] ?? null,
                whatsapp: $datos['whatsapp'] ?? null,
                zonaHoraria: $datos['zona_horaria'] ?? 'America/Bogota',
            ),
            $this->usuarioActual(),
        );

        $negocio->update([
            'paleta_marca_id' => $datos['paleta_marca_id'] ?? null,
            'permite_editar_pagina' => $datos['permite_editar_pagina'] ?? false,
        ]);

        return response()->json(['data' => new RecursoNegocioDetalle($negocio->refresh())], 201);
    }

    public function update(SolicitudActualizarNegocio $solicitud, Negocio $negocio): JsonResponse
    {
        $negocio->update($solicitud->validated());

        return response()->json(['data' => new RecursoNegocioDetalle($negocio)]);
    }

    public function destroy(Negocio $negocio): JsonResponse
    {
        $negocio->delete();

        return response()->json(['mensaje' => 'Negocio eliminado.']);
    }

    public function entrarComoNegocio(Negocio $negocio): JsonResponse
    {
        app(SuplantarNegocio::class)->ejecutar($negocio, $this->usuarioActual());

        return response()->json(['url' => route('app.panel', $negocio)]);
    }

    public function registrarPago(SolicitudRegistrarPagoSuscripcion $solicitud, Negocio $negocio): JsonResponse
    {
        $datos = $solicitud->validated();

        app(RegistrarPagoSuscripcion::class)->ejecutar(new DatosPagoSuscripcion(
            negocio: $negocio,
            fecha: Carbon::parse($datos['fecha']),
            valor: (int) $datos['valor'],
            metodo: $datos['metodo'],
            periodoDesde: Carbon::parse($datos['periodo_desde']),
            periodoHasta: Carbon::parse($datos['periodo_hasta']),
            registradoPor: $this->usuarioActual(),
        ));

        return response()->json(['data' => new RecursoNegocioDetalle($negocio->refresh())]);
    }

    public function cambiarEstado(SolicitudCambiarEstadoNegocio $solicitud, Negocio $negocio): JsonResponse
    {
        $datos = $solicitud->validated();

        app(CambiarEstadoNegocio::class)->ejecutar(new DatosCambioEstadoNegocio(
            negocio: $negocio,
            estadoDestino: EstadoSuscripcion::from($datos['estado_destino']),
            motivo: $datos['motivo'],
            cambiadoPor: $this->usuarioActual(),
        ));

        return response()->json(['data' => new RecursoNegocioDetalle($negocio->refresh())]);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
