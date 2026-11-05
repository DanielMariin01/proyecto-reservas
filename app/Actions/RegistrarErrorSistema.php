<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\RegistroError;
use App\Models\Usuario;
use App\Notifications\AlertaErrorRepetido;
use App\Observabilidad\ContextoObservabilidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * RNF-OBS-03: guarda cada excepción reportada (bootstrap/app.php, dentro
 * de withExceptions()->report()) para que el panel de salud tenga algo
 * que listar y filtrar por negocio. RNF-OBS-04: si la misma excepción en
 * la misma ruta se repite varias veces en poco tiempo, avisa al super
 * admin por correo (solo para 5xx — un 404/403 que se repite suele ser
 * tráfico normal, no algo que necesite una alerta).
 */
final class RegistrarErrorSistema
{
    private const UMBRAL_REPETICION = 5;

    private const VENTANA_REPETICION_MINUTOS = 60;

    private const ENFRIAMIENTO_ALERTA_MINUTOS = 60;

    public function __construct(
        private readonly ContextoObservabilidad $contexto,
        private readonly GenerarCodigoDeReferencia $generarCodigo,
    ) {}

    public function ejecutar(Throwable $excepcion, Request $peticion): RegistroError
    {
        $idPeticion = $this->contexto->idPeticion();
        $estado = $excepcion instanceof HttpExceptionInterface ? $excepcion->getStatusCode() : 500;

        $registro = RegistroError::create([
            'negocio_id' => $this->contexto->negocioId(),
            'usuario_id' => $this->contexto->usuarioId(),
            'id_peticion' => $idPeticion,
            'codigo_referencia' => $this->generarCodigo->ejecutar($idPeticion ?? ''),
            'clase_excepcion' => $excepcion::class,
            'mensaje' => $excepcion->getMessage() !== '' ? $excepcion->getMessage() : $excepcion::class,
            'ruta' => $peticion->path(),
            'metodo_http' => $peticion->method(),
            'estado_http' => $estado,
        ]);

        $this->avisarSiSeRepite($registro);

        return $registro;
    }

    private function avisarSiSeRepite(RegistroError $registro): void
    {
        if ($registro->estado_http < 500) {
            return;
        }

        $repeticiones = RegistroError::query()
            ->where('clase_excepcion', $registro->clase_excepcion)
            ->where('ruta', $registro->ruta)
            ->where('created_at', '>=', now()->subMinutes(self::VENTANA_REPETICION_MINUTOS))
            ->count();

        if ($repeticiones < self::UMBRAL_REPETICION) {
            return;
        }

        $clave = 'alerta-error-repetido:'.md5($registro->clase_excepcion.'|'.(string) $registro->ruta);

        if (Cache::has($clave)) {
            return;
        }

        Cache::put($clave, true, now()->addMinutes(self::ENFRIAMIENTO_ALERTA_MINUTOS));

        Notification::send(
            Usuario::where('es_super_admin', true)->get(),
            new AlertaErrorRepetido($registro, $repeticiones),
        );
    }
}
