<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Enums\AutorizacionFotos;
use App\Enums\DecisionValoracion;
use App\Enums\FormaUna;
use App\Enums\LargoUna;
use App\Enums\ServicioDiseno;
use App\Models\Cliente;
use App\Models\Profesional;
use Carbon\CarbonImmutable;

final readonly class DatosHistoriaClinica
{
    /**
     * @param  list<string>  $piel
     * @param  list<string>  $una
     * @param  list<string>  $hallazgosVisibles
     */
    public function __construct(
        public Cliente $cliente,
        public Profesional $profesional,
        public CarbonImmutable $fecha,
        // Seccion 2.
        public bool $alergias,
        public bool $reaccionAnterior,
        public bool $diabetes,
        public bool $problemasCirculacion,
        public bool $medicamentos,
        public bool $heridasActuales,
        public ?string $otraCondicion,
        public ?string $detalleReaccion,
        // Seccion 3.
        public array $piel,
        public array $una,
        public array $hallazgosVisibles,
        public ?string $hallazgoOtroTexto,
        public ?string $observacionesValoracion,
        public DecisionValoracion $decision,
        public ?string $motivoDecision,
        // Seccion 4.
        public ServicioDiseno $servicioDiseno,
        public ?string $servicioDisenoOtroTexto,
        public FormaUna $formaUna,
        public LargoUna $largoUna,
        public ?string $colores,
        public ?string $decoracionReferencia,
        // Seccion 5.
        public string $horaInicio,
        public string $horaFinal,
        public ?string $tecnicaRealizada,
        public ?string $productosMarcas,
        public ?string $loteVencimiento,
        public bool $retiroPrevio,
        public bool $reparaciones,
        public ?string $reparacionesDetalle,
        public ?string $cambiosDuranteServicio,
        public ?string $novedadesIncidentes,
        // Seccion 6.
        public int $valor,
        public int $abono,
        public int $saldo,
        public bool $revisionResultado,
        public bool $recibioCuidados,
        public ?CarbonImmutable $fechaSeguimiento,
        public ?CarbonImmutable $proximoMantenimientoSugerido,
        public bool $novedadPendiente,
        // Seccion 7.
        public bool $consentimientoAceptado,
        public AutorizacionFotos $autorizacionFotos,
        public ?string $firmaClientaBase64,
        public ?string $firmaProfesionalBase64,
    ) {}
}
