<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\CrearReserva;
use App\DatosTransferencia\DatosReserva;
use App\Enums\OrigenCita;
use App\Enums\Permiso;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\HoraYaTomadaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudCrearCita;
use App\Http\Resources\App\RecursoCita;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\ServicioAccionesCita;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * RF-RES-06: la lista general de citas del negocio, con creación manual
 * desde el panel (recepción/dueño). Las acciones sobre cada cita viven
 * en CitaAccionesController, compartidas con la agenda del día.
 */
final class CitaController extends Controller
{
    public function __construct(private readonly ServicioAccionesCita $servicioAccionesCita) {}

    public function index(Request $request, Negocio $negocio): JsonResponse
    {
        $this->authorize('viewAny', Cita::class);
        $usuario = $this->usuarioActual();
        $profesional = $usuario->profesional;
        $profesionalId = $profesional === null ? 0 : $profesional->id;

        $citas = Cita::query()
            ->when(
                ! $usuario->es_super_admin && ! $usuario->can(Permiso::GestionarCitasTodas->value),
                fn (Builder $query): Builder => $query->where('profesional_id', $profesionalId),
            )
            ->when(
                $request->filled('profesional_id'),
                fn (Builder $query): Builder => $query->where('profesional_id', $request->integer('profesional_id')),
            )
            ->when(
                $request->filled('estado'),
                fn (Builder $query): Builder => $query->where('estado', $request->string('estado')),
            )
            ->orderByDesc('inicio')
            ->paginate(25);

        return response()->json([
            'data' => $citas->getCollection()
                ->map(fn (Cita $cita): RecursoCita => new RecursoCita($cita, $usuario, $this->servicioAccionesCita))
                ->all(),
            'meta' => [
                'current_page' => $citas->currentPage(),
                'last_page' => $citas->lastPage(),
            ],
        ]);
    }

    public function store(SolicitudCrearCita $solicitud, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Cita::class);
        $datos = $solicitud->validated();

        try {
            $cita = app(CrearReserva::class)->ejecutar(new DatosReserva(
                negocio: $negocio,
                servicio: Servicio::query()->findOrFail((int) $datos['servicio_id']),
                nombreCliente: $datos['nombre_cliente'],
                celularCliente: $datos['celular_cliente'],
                correoCliente: $datos['correo_cliente'] ?? null,
                inicio: CarbonImmutable::parse("{$datos['fecha']} {$datos['hora']}", $negocio->zona_horaria)->utc(),
                origen: OrigenCita::Panel,
                profesional: filled($datos['profesional_id'] ?? null) ? Profesional::query()->findOrFail((int) $datos['profesional_id']) : null,
                notas: $datos['notas'] ?? null,
                forzar: (bool) ($datos['forzar'] ?? false),
            ));
        } catch (HoraYaTomadaException|HoraInvalidaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoCita($cita, $this->usuarioActual(), $this->servicioAccionesCita)], 201);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
