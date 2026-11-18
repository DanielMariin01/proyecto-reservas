<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Disponibilidad\BloqueLibre;
use App\Enums\FranjaHoraria;
use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Http\Resources\App\RecursoCitaDelDia;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Services\ServicioPanelDelDia;
use App\Support\Hora;
use Carbon\CarbonImmutable;
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
     * @return list<array{profesionalId: int, profesionalNombre: string, bloques: list<array{franja: string, texto: string}>}>
     */
    private function formatearHuecosLibres(Negocio $negocio): array
    {
        $zonaHoraria = $negocio->zona_horaria;
        $fechaLocal = CarbonImmutable::now($zonaHoraria)->startOfDay();

        return array_values(
            $this->servicioPanelDelDia->huecosLibres($negocio)
                ->map(function (array $columna) use ($zonaHoraria, $fechaLocal): array {
                    /** @var Profesional $profesional */
                    $profesional = $columna['profesional'];
                    /** @var Collection<int, BloqueLibre> $bloques */
                    $bloques = $columna['bloques'];

                    return [
                        'profesionalId' => $profesional->id,
                        'profesionalNombre' => $profesional->nombre,
                        'bloques' => $this->repartirPorFranja($bloques, $fechaLocal, $zonaHoraria),
                    ];
                })
                ->all(),
        );
    }

    /**
     * Un hueco libre que cruza dos franjas (ej. 11:00 a. m.-1:00 p. m.) se
     * reparte entre ambas en vez de quedar entero en la de inicio: cada
     * franja solo muestra lo que realmente le pertenece.
     *
     * @param  Collection<int, BloqueLibre>  $bloques
     * @return list<array{franja: string, texto: string}>
     */
    private function repartirPorFranja(Collection $bloques, CarbonImmutable $fechaLocal, string $zonaHoraria): array
    {
        $resultado = [];

        foreach (FranjaHoraria::cases() as $franja) {
            [$inicioFranja, $finFranja] = $franja->rangoUtc($fechaLocal);

            foreach ($bloques as $bloque) {
                $interseccion = $bloque->interseccionCon($inicioFranja, $finFranja);

                if ($interseccion === null) {
                    continue;
                }

                $resultado[] = [
                    'franja' => $franja->value,
                    'texto' => Hora::formatear($interseccion->inicio, $zonaHoraria).' – '.Hora::formatear($interseccion->fin, $zonaHoraria),
                ];
            }
        }

        return $resultado;
    }

    private function autorizar(): void
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::guard('web')->user();

        abort_if($usuario === null, 403);
        abort_unless($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value), 403);
    }
}
