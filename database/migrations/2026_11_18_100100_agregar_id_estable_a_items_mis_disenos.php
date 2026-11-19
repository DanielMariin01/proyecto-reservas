<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * RF-HCL-08: "Diseños favoritos" en /mi-cuenta necesita una referencia
 * estable a cada ítem del bloque `mis_disenos`, que hasta ahora era un
 * array sin identificador propio. Esta migración de datos (no de
 * esquema) le agrega un `id` (ULID) a cada ítem que todavía no lo tenga,
 * tanto en el borrador (`bloques`) como en lo publicado
 * (`bloques_publicados`), sin tocar el resto de la estructura.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('paginas')->orderBy('id')->each(function (object $pagina): void {
            DB::table('paginas')->where('id', $pagina->id)->update([
                'bloques' => $this->conIdsEstables($pagina->bloques),
                'bloques_publicados' => $this->conIdsEstables($pagina->bloques_publicados),
            ]);
        });
    }

    public function down(): void
    {
        // No hay nada que revertir: quitar los ids no afecta ninguna
        // funcionalidad anterior a esta tarea.
    }

    private function conIdsEstables(?string $bloquesJson): ?string
    {
        if ($bloquesJson === null) {
            return null;
        }

        $bloques = json_decode($bloquesJson, true);

        if (! is_array($bloques)) {
            return $bloquesJson;
        }

        foreach ($bloques as &$bloque) {
            if (($bloque['type'] ?? null) !== 'mis_disenos' || ! is_array($bloque['data']['items'] ?? null)) {
                continue;
            }

            foreach ($bloque['data']['items'] as &$item) {
                if (is_array($item) && empty($item['id'])) {
                    $item['id'] = (string) Str::ulid();
                }
            }
        }

        return json_encode($bloques);
    }
};
