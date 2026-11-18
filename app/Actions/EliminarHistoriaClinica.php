<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contratos\ServicioFirmaDigital;
use App\Events\HistoriaClinicaEliminada;
use App\Models\HistoriaClinica;
use App\Models\Usuario;

final class EliminarHistoriaClinica
{
    public function __construct(
        private readonly ServicioFirmaDigital $servicioFirmaDigital,
    ) {}

    public function ejecutar(HistoriaClinica $historiaClinica, ?Usuario $eliminadaPor = null): void
    {
        $historiaClinicaId = $historiaClinica->id;
        $negocioId = $historiaClinica->negocio_id;
        $clienteId = $historiaClinica->cliente_id;
        $profesionalId = $historiaClinica->profesional_id;

        $this->servicioFirmaDigital->eliminar($historiaClinica->firma_clienta_path);
        $this->servicioFirmaDigital->eliminar($historiaClinica->firma_profesional_path);

        $historiaClinica->delete();

        HistoriaClinicaEliminada::dispatch($historiaClinicaId, $negocioId, $clienteId, $profesionalId, $eliminadaPor);
    }
}
