<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cliente\RecursoHistoriaClinicaDeCuenta;
use App\Models\CuentaCliente;
use App\Models\HistoriaClinica;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * RF-HCL-08-a: el historial clínico propio, de solo lectura. Cruza
 * negocios igual que CitasClienteController::index, filtrando por las
 * Cliente enlazadas a esta cuenta en vez de por un negocio puntual.
 */
final class HistorialClinicoController extends Controller
{
    public function index(): JsonResponse
    {
        $cuenta = $this->cuentaActual();

        $historias = HistoriaClinica::query()
            ->whereHas('cliente', fn ($consulta) => $consulta->where('cuenta_cliente_id', $cuenta->id))
            ->with('profesional')
            ->orderByDesc('fecha')
            ->get();

        return response()->json(['data' => RecursoHistoriaClinicaDeCuenta::collection($historias)]);
    }

    public function mostrarFirmaClienta(int $historiaClinica): StreamedResponse
    {
        return $this->mostrarFirma($historiaClinica, 'firma_clienta_path');
    }

    public function mostrarFirmaProfesional(int $historiaClinica): StreamedResponse
    {
        return $this->mostrarFirma($historiaClinica, 'firma_profesional_path');
    }

    private function mostrarFirma(int $historiaClinica, string $columna): StreamedResponse
    {
        $historiaClinicaModelo = HistoriaClinica::query()->findOrFail($historiaClinica);
        abort_if($historiaClinicaModelo->cliente?->cuenta_cliente_id !== $this->cuentaActual()->id, 403);

        $ruta = $historiaClinicaModelo->{$columna};
        abort_if($ruta === null, 404);

        return Storage::disk('local')->response($ruta);
    }

    private function cuentaActual(): CuentaCliente
    {
        /** @var CuentaCliente $cuenta */
        $cuenta = Auth::guard('cliente')->user();

        return $cuenta;
    }
}
