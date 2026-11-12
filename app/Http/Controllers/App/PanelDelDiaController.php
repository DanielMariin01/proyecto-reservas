<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Disponibilidad\BloqueLibre;
use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Http\Resources\App\RecursoCitaDelDia;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Services\ServicioPanelDelDia;
use App\Support\Hora;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

final class PanelDelDiaController extends Controller
{
    public function __construct(private readonly ServicioPanelDelDia $servicioPanelDelDia) {}

    public function mostrar(Negocio $negocio): JsonResponse
    {
        $this->autorizar();

        $citasDeHoy = $this->servicioPanelDelDia->citasDeHoy($negocio);
        $citasProximas = $this->servicioPanelDelDia->citasProximas($citasDeHoy);

        return response()->json([
            'totalCitasHoy' => $citasDeHoy->count(),
            'totalProximas' => $citasProximas->count(),
            'ingresosRegistrados' => $this->servicioPanelDelDia->ingresosRegistrados($negocio),
            'citas' => RecursoCitaDelDia::collection($citasDeHoy),
            'huecosLibres' => $this->formatearHuecosLibres($negocio),
        ]);
    }

    /**
     * @return list<array{profesionalId: int, profesionalNombre: string, bloques: list<string>}>
     */
    private function formatearHuecosLibres(Negocio $negocio): array
    {
        $zonaHoraria = $negocio->zona_horaria;

        return array_values(
            $this->servicioPanelDelDia->huecosLibres($negocio)
                ->map(function (array $columna) use ($zonaHoraria): array {
                    /** @var Profesional $profesional */
                    $profesional = $columna['profesional'];
                    /** @var Collection<int, BloqueLibre> $bloques */
                    $bloques = $columna['bloques'];

                    return [
                        'profesionalId' => $profesional->id,
                        'profesionalNombre' => $profesional->nombre,
                        'bloques' => array_values(
                            $bloques
                                ->map(fn (BloqueLibre $bloque): string => Hora::formatear($bloque->inicio, $zonaHoraria).' – '.Hora::formatear($bloque->fin, $zonaHoraria))
                                ->all(),
                        ),
                    ];
                })
                ->all(),
        );
    }

    private function autorizar(): void
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::guard('web')->user();

        abort_if($usuario === null, 403);
        abort_unless($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value), 403);
    }
}
