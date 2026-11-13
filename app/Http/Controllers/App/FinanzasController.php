<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\EliminarGasto;
use App\Actions\RegistrarGasto;
use App\DatosTransferencia\DatosNuevoGasto;
use App\Enums\RangoFinanzas;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudNuevoGasto;
use App\Models\Gasto;
use App\Models\Negocio;
use App\Models\Usuario;
use App\Services\ServicioFinanzas;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class FinanzasController extends Controller
{
    public function __construct(private readonly ServicioFinanzas $servicioFinanzas) {}

    public function mostrar(Request $request, Negocio $negocio): JsonResponse
    {
        $this->authorize('viewAny', Gasto::class);

        $datos = $request->validate([
            'rango' => ['nullable', Rule::in(array_column(RangoFinanzas::cases(), 'value'))],
        ]);

        $rango = isset($datos['rango']) ? RangoFinanzas::from($datos['rango']) : RangoFinanzas::Mes;
        [$inicioLocal, $finLocal] = $this->servicioFinanzas->rangoLocal($rango, $negocio);

        $pagosDelRango = $this->servicioFinanzas->pagosDelRango($negocio, $inicioLocal, $finLocal);
        $gastosDelRango = $this->servicioFinanzas->gastosDelRango($negocio, $inicioLocal, $finLocal);

        $ingresosBrutos = $this->servicioFinanzas->ingresosBrutos($pagosDelRango);
        $totalGastos = $this->servicioFinanzas->totalGastos($gastosDelRango);

        return response()->json([
            'rango' => $rango->value,
            'ingresosBrutos' => $ingresosBrutos,
            'totalPropinas' => $this->servicioFinanzas->totalPropinas($pagosDelRango),
            'totalGastos' => $totalGastos,
            'neto' => $ingresosBrutos - $totalGastos,
            'ingresosPorMetodo' => $this->servicioFinanzas->ingresosPorMetodo($pagosDelRango),
            'tendencia' => $this->servicioFinanzas->tendencia(
                $rango,
                $inicioLocal,
                $finLocal,
                $pagosDelRango,
                $gastosDelRango,
                $negocio->zona_horaria,
            ),
            'gastos' => $gastosDelRango->map(fn (Gasto $gasto): array => [
                'id' => $gasto->id,
                'monto' => $gasto->monto,
                'descripcion' => $gasto->descripcion,
                'fecha' => $gasto->fecha->toDateString(),
            ])->values(),
        ]);
    }

    public function registrarGasto(SolicitudNuevoGasto $solicitud, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Gasto::class);

        $datos = $solicitud->validated();

        $gasto = app(RegistrarGasto::class)->ejecutar(
            new DatosNuevoGasto(
                negocio: $negocio,
                monto: (int) $datos['monto'],
                descripcion: $datos['descripcion'],
                fecha: CarbonImmutable::parse($datos['fecha']),
            ),
            $this->usuarioActual(),
        );

        return response()->json(['data' => [
            'id' => $gasto->id,
            'monto' => $gasto->monto,
            'descripcion' => $gasto->descripcion,
            'fecha' => $gasto->fecha->toDateString(),
        ]], 201);
    }

    public function eliminarGasto(Negocio $negocio, int $gasto): JsonResponse
    {
        $this->authorize('delete', Gasto::class);

        $gastoModelo = Gasto::query()->findOrFail($gasto);

        app(EliminarGasto::class)->ejecutar($gastoModelo, $this->usuarioActual());

        return response()->json(['mensaje' => 'Gasto eliminado.']);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
