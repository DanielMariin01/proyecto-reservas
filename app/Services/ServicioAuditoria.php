<?php

declare(strict_types=1);

namespace App\Services;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\RegistroAuditoria;

final class ServicioAuditoria
{
    public function registrar(DatosAuditoria $datos): RegistroAuditoria
    {
        return RegistroAuditoria::create([
            'negocio_id' => $datos->negocio?->id,
            'usuario_id' => $datos->usuario?->id,
            'accion' => $datos->accion,
            'modelo' => $datos->modelo,
            'modelo_id' => $datos->modeloId,
            'valores_antes' => $datos->valoresAntes,
            'valores_despues' => $datos->valoresDespues,
            'motivo' => $datos->motivo,
            'hecho_por_soporte' => $this->seHizoDesdeUnaSuplantacion(),
        ]);
    }

    /**
     * El marcado de "hecho por soporte" (RF-SUP-03) se decide aquí, leyendo la
     * sesión actual, en vez de que cada evento lo declare por su cuenta. Así
     * cualquier acción futura que dispare un evento auditable queda marcada
     * automáticamente mientras el super admin esté suplantando un negocio.
     */
    private function seHizoDesdeUnaSuplantacion(): bool
    {
        return session()->has('negocio_suplantado_id');
    }
}
