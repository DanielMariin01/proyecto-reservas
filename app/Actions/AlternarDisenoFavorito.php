<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CuentaCliente;
use App\Models\DisenoFavorito;
use App\Models\Negocio;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * RF-HCL-08: marcar/desmarcar un ítem del bloque público `mis_disenos`
 * como favorito. Antes de guardar, confirma que el ítem todavía existe
 * en la página publicada del negocio — así un `diseno_id` inventado o ya
 * borrado por el super admin nunca llega a crear un favorito huérfano.
 */
final class AlternarDisenoFavorito
{
    public function ejecutar(CuentaCliente $cuenta, Negocio $negocio, string $disenoId): bool
    {
        if (! $this->existeEnLaPaginaPublicada($negocio, $disenoId)) {
            throw new ModelNotFoundException("El diseño {$disenoId} no existe en la página de este negocio.");
        }

        $favorito = DisenoFavorito::query()
            ->where('cuenta_cliente_id', $cuenta->id)
            ->where('negocio_id', $negocio->id)
            ->where('diseno_id', $disenoId)
            ->first();

        if ($favorito !== null) {
            $favorito->delete();

            return false;
        }

        DisenoFavorito::create([
            'cuenta_cliente_id' => $cuenta->id,
            'negocio_id' => $negocio->id,
            'diseno_id' => $disenoId,
        ]);

        return true;
    }

    private function existeEnLaPaginaPublicada(Negocio $negocio, string $disenoId): bool
    {
        $pagina = $negocio->pagina;
        $bloques = $pagina === null ? [] : ($pagina->bloques_publicados ?? []);

        foreach ($bloques as $bloque) {
            if (($bloque['type'] ?? null) !== 'mis_disenos') {
                continue;
            }

            foreach ($bloque['data']['items'] ?? [] as $item) {
                if (($item['id'] ?? null) === $disenoId) {
                    return true;
                }
            }
        }

        return false;
    }
}
