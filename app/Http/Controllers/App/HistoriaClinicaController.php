<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\ActualizarHistoriaClinica;
use App\Actions\CrearHistoriaClinica;
use App\Actions\EliminarHistoriaClinica;
use App\DatosTransferencia\DatosHistoriaClinica;
use App\Enums\AutorizacionFotos;
use App\Enums\DecisionValoracion;
use App\Enums\FormaUna;
use App\Enums\LargoUna;
use App\Enums\ServicioDiseno;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudHistoriaClinica;
use App\Http\Resources\App\RecursoHistoriaClinica;
use App\Models\Cliente;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Policies\PoliticaHistoriaClinica;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class HistoriaClinicaController extends Controller
{
    /**
     * El parametro Negocio $negocio no se usa directamente en varios de
     * estos metodos, pero es necesario: Laravel solo resuelve el binding
     * implicito de {negocio:slug} cuando el metodo del controlador lo
     * tipa, y ResolverNegocioDesdeRuta depende de que ese binding ya
     * este resuelto para fijar el contexto del negocio actual.
     */
    public function index(Negocio $negocio, int $cliente): AnonymousResourceCollection
    {
        $this->authorize('viewAny', HistoriaClinica::class);

        $clienteModelo = Cliente::query()->findOrFail($cliente);
        $profesionalIdFiltro = app(PoliticaHistoriaClinica::class)->profesionalIdParaFiltrarListado($this->usuarioActual());

        $historias = HistoriaClinica::query()
            ->where('cliente_id', $clienteModelo->id)
            ->when(
                $profesionalIdFiltro !== null,
                fn (Builder $consulta): Builder => $consulta->where('profesional_id', $profesionalIdFiltro),
            )
            ->with('profesional')
            ->orderByDesc('fecha')
            ->get();

        return RecursoHistoriaClinica::collection($historias);
    }

    public function store(SolicitudHistoriaClinica $solicitud, Negocio $negocio, int $cliente): JsonResponse
    {
        $this->authorize('create', HistoriaClinica::class);

        $clienteModelo = Cliente::query()->findOrFail($cliente);
        $datos = $this->datosDesdeSolicitud($solicitud, $clienteModelo);

        $historiaClinica = app(CrearHistoriaClinica::class)->ejecutar($datos, $this->usuarioActual());

        return response()->json(['data' => new RecursoHistoriaClinica($historiaClinica)], 201);
    }

    public function update(SolicitudHistoriaClinica $solicitud, Negocio $negocio, int $cliente, int $historiaClinica): JsonResponse
    {
        $historiaClinicaModelo = HistoriaClinica::query()->findOrFail($historiaClinica);
        $this->authorize('update', $historiaClinicaModelo);

        $clienteModelo = Cliente::query()->findOrFail($cliente);
        $datos = $this->datosDesdeSolicitud($solicitud, $clienteModelo);

        $historiaClinicaModelo = app(ActualizarHistoriaClinica::class)->ejecutar($historiaClinicaModelo, $datos, $this->usuarioActual());

        return response()->json(['data' => new RecursoHistoriaClinica($historiaClinicaModelo)]);
    }

    public function destroy(Negocio $negocio, int $cliente, int $historiaClinica): JsonResponse
    {
        $historiaClinicaModelo = HistoriaClinica::query()->findOrFail($historiaClinica);
        $this->authorize('delete', $historiaClinicaModelo);

        app(EliminarHistoriaClinica::class)->ejecutar($historiaClinicaModelo, $this->usuarioActual());

        return response()->json(['mensaje' => 'Historia clínica eliminada.']);
    }

    public function mostrarFirmaClienta(Negocio $negocio, int $historiaClinica): StreamedResponse
    {
        return $this->mostrarFirma($historiaClinica, 'firma_clienta_path');
    }

    public function mostrarFirmaProfesional(Negocio $negocio, int $historiaClinica): StreamedResponse
    {
        return $this->mostrarFirma($historiaClinica, 'firma_profesional_path');
    }

    private function mostrarFirma(int $historiaClinica, string $columna): StreamedResponse
    {
        $historiaClinicaModelo = HistoriaClinica::query()->findOrFail($historiaClinica);
        $this->authorize('view', $historiaClinicaModelo);

        $ruta = $historiaClinicaModelo->{$columna};
        abort_if($ruta === null, 404);

        return Storage::disk('local')->response($ruta);
    }

    private function datosDesdeSolicitud(SolicitudHistoriaClinica $solicitud, Cliente $cliente): DatosHistoriaClinica
    {
        $datos = $solicitud->validated();
        $profesional = Profesional::query()->findOrFail((int) $datos['profesional_id']);

        return new DatosHistoriaClinica(
            cliente: $cliente,
            profesional: $profesional,
            fecha: CarbonImmutable::parse($datos['fecha']),
            alergias: (bool) ($datos['alergias'] ?? false),
            reaccionAnterior: (bool) ($datos['reaccion_anterior'] ?? false),
            diabetes: (bool) ($datos['diabetes'] ?? false),
            problemasCirculacion: (bool) ($datos['problemas_circulacion'] ?? false),
            medicamentos: (bool) ($datos['medicamentos'] ?? false),
            heridasActuales: (bool) ($datos['heridas_actuales'] ?? false),
            otraCondicion: $datos['otra_condicion'] ?? null,
            detalleReaccion: $datos['detalle_reaccion'] ?? null,
            piel: $datos['piel'],
            una: $datos['una'],
            hallazgosVisibles: $datos['hallazgos_visibles'] ?? [],
            hallazgoOtroTexto: $datos['hallazgo_otro_texto'] ?? null,
            observacionesValoracion: $datos['observaciones_valoracion'] ?? null,
            decision: DecisionValoracion::from($datos['decision']),
            motivoDecision: $datos['motivo_decision'] ?? null,
            servicioDiseno: ServicioDiseno::from($datos['servicio_diseno']),
            servicioDisenoOtroTexto: $datos['servicio_diseno_otro_texto'] ?? null,
            formaUna: FormaUna::from($datos['forma_una']),
            largoUna: LargoUna::from($datos['largo_una']),
            colores: $datos['colores'] ?? null,
            decoracionReferencia: $datos['decoracion_referencia'] ?? null,
            horaInicio: $datos['hora_inicio'],
            horaFinal: $datos['hora_final'],
            tecnicaRealizada: $datos['tecnica_realizada'] ?? null,
            productosMarcas: $datos['productos_marcas'] ?? null,
            loteVencimiento: $datos['lote_vencimiento'] ?? null,
            retiroPrevio: (bool) ($datos['retiro_previo'] ?? false),
            reparaciones: (bool) ($datos['reparaciones'] ?? false),
            reparacionesDetalle: $datos['reparaciones_detalle'] ?? null,
            cambiosDuranteServicio: $datos['cambios_durante_servicio'] ?? null,
            novedadesIncidentes: $datos['novedades_incidentes'] ?? null,
            valor: (int) $datos['valor'],
            abono: (int) $datos['abono'],
            saldo: (int) $datos['saldo'],
            revisionResultado: (bool) ($datos['revision_resultado'] ?? false),
            recibioCuidados: (bool) ($datos['recibio_cuidados'] ?? false),
            fechaSeguimiento: isset($datos['fecha_seguimiento']) ? CarbonImmutable::parse($datos['fecha_seguimiento']) : null,
            proximoMantenimientoSugerido: isset($datos['proximo_mantenimiento_sugerido'])
                ? CarbonImmutable::parse($datos['proximo_mantenimiento_sugerido'])
                : null,
            novedadPendiente: (bool) ($datos['novedad_pendiente'] ?? false),
            consentimientoAceptado: (bool) ($datos['consentimiento_aceptado'] ?? false),
            autorizacionFotos: AutorizacionFotos::from($datos['autorizacion_fotos']),
            firmaClientaBase64: $datos['firma_clienta_base64'] ?? null,
            firmaProfesionalBase64: $datos['firma_profesional_base64'] ?? null,
        );
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
