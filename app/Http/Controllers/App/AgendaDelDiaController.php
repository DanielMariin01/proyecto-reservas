<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Http\Resources\App\RecursoCita;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Services\ServicioAccionesCita;
use App\Support\Fecha;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * RF-RES-07: la agenda visual del dueño, columnas por profesional. Las
 * acciones sobre cada cita viven en CitaAccionesController, compartidas
 * con la lista general de citas — nunca duplicar esa lógica.
 */
final class AgendaDelDiaController extends Controller
{
    public function __construct(private readonly ServicioAccionesCita $servicioAccionesCita) {}

    public function mostrar(Request $request, Negocio $negocio): JsonResponse
    {
        $this->autorizar();

        $fecha = $request->query('fecha');
        $fechaLocal = is_string($fecha) && $fecha !== ''
            ? CarbonImmutable::parse($fecha, $negocio->zona_horaria)
            : CarbonImmutable::now($negocio->zona_horaria);
        $fechaLocal = $fechaLocal->startOfDay();

        $inicioDiaUtc = $fechaLocal->utc();
        $finDiaUtc = $fechaLocal->endOfDay()->utc();
        $usuario = $this->usuarioActual();

        $columnas = Profesional::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Profesional $profesional): array => [
                'profesionalId' => $profesional->id,
                'profesionalNombre' => $profesional->nombre,
                'profesionalColor' => $profesional->color,
                'citas' => Cita::query()
                    ->where('profesional_id', $profesional->id)
                    ->where('inicio', '>=', $inicioDiaUtc)
                    ->where('inicio', '<', $finDiaUtc)
                    ->orderBy('inicio')
                    ->get()
                    ->map(fn (Cita $cita): RecursoCita => new RecursoCita($cita, $usuario, $this->servicioAccionesCita)),
            ])
            ->values();

        return response()->json([
            'fecha' => $fechaLocal->toDateString(),
            'fechaEtiqueta' => Fecha::formatear($fechaLocal, $negocio->zona_horaria),
            'columnas' => $columnas,
        ]);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }

    private function autorizar(): void
    {
        $usuario = Auth::guard('web')->user();

        abort_if($usuario === null, 403);
        abort_unless($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value), 403);
    }
}
