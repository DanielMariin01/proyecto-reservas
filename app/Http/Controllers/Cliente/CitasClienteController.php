<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cliente;

use App\Actions\CancelarCita;
use App\Actions\CrearResena;
use App\Actions\ReprogramarCita;
use App\DatosTransferencia\DatosNuevaResena;
use App\DatosTransferencia\DatosReprogramacion;
use App\Exceptions\FueraDePoliticaDeCancelacionException;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\HoraYaTomadaException;
use App\Exceptions\MaximoDeReprogramacionesException;
use App\Exceptions\ResenaNoElegibleException;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cliente\RecursoCitaDeCuenta;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\CuentaCliente;
use App\Services\ServicioEnlaceCita;
use App\Services\ServicioResenas;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class CitasClienteController extends Controller
{
    public function __construct(
        private readonly ServicioEnlaceCita $servicioEnlaceCita,
        private readonly ServicioResenas $servicioResenas,
        private readonly CancelarCita $cancelarCita,
        private readonly ReprogramarCita $reprogramarCita,
        private readonly CrearResena $crearResena,
    ) {}

    public function index(): JsonResponse
    {
        $cuenta = $this->cuentaActual();

        $citas = Cita::query()
            ->whereHas('cliente', fn ($consulta) => $consulta->where('cuenta_cliente_id', $cuenta->id))
            ->orderByDesc('inicio')
            ->get();

        $datos = $citas
            ->map(fn (Cita $cita): RecursoCitaDeCuenta => new RecursoCitaDeCuenta($cita, $this->servicioEnlaceCita, $this->servicioResenas))
            ->values();

        return response()->json(['data' => $datos]);
    }

    public function cancelar(Request $request, Cita $cita): JsonResponse
    {
        $this->autorizar($cita);
        $request->validate(['motivo' => ['nullable', 'string', 'max:500']]);

        $motivo = $request->filled('motivo') ? (string) $request->string('motivo') : 'Cancelada por el cliente desde su cuenta';

        try {
            $this->cancelarCita->ejecutar($cita, $motivo);
        } catch (FueraDePoliticaDeCancelacionException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage(), 'mostrarWhatsapp' => true], 422);
        } catch (TransicionDeEstadoInvalidaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return $this->respuesta($cita);
    }

    public function reprogramar(Request $request, Cita $cita): JsonResponse
    {
        $this->autorizar($cita);
        $request->validate(['inicio' => ['required', 'date']]);

        $nuevoInicio = CarbonImmutable::parse((string) $request->string('inicio'))->utc();

        try {
            $this->reprogramarCita->ejecutar(new DatosReprogramacion(cita: $cita, nuevoInicio: $nuevoInicio));
        } catch (MaximoDeReprogramacionesException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage(), 'mostrarWhatsapp' => true], 422);
        } catch (HoraInvalidaException|HoraYaTomadaException|TransicionDeEstadoInvalidaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return $this->respuesta($cita);
    }

    public function resena(Request $request, Cita $cita): JsonResponse
    {
        $this->autorizar($cita);
        $request->validate([
            'calificacion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        try {
            $this->crearResena->ejecutar(new DatosNuevaResena(
                cita: $cita,
                calificacion: $request->integer('calificacion'),
                comentario: (string) $request->string('comentario'),
            ));
        } catch (ResenaNoElegibleException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return $this->respuesta($cita);
    }

    private function respuesta(Cita $cita): JsonResponse
    {
        return response()->json(['data' => new RecursoCitaDeCuenta($cita, $this->servicioEnlaceCita, $this->servicioResenas)]);
    }

    private function autorizar(Cita $cita): void
    {
        $cuenta = $this->cuentaActual();
        $cliente = Cliente::findOrFail($cita->cliente_id);

        abort_if($cliente->cuenta_cliente_id !== $cuenta->id, 403);
    }

    private function cuentaActual(): CuentaCliente
    {
        /** @var CuentaCliente $cuenta */
        $cuenta = Auth::guard('cliente')->user();

        return $cuenta;
    }
}
