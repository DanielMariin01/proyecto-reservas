<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TipoBloque;
use App\Models\Pagina;

/**
 * RF-PAG-01: el dueño edita el texto y las fotos de los bloques que el
 * super admin ya dejó armados (nunca agrega, quita ni reordena bloques,
 * ni cambia colores). Por tipo de bloque, los únicos campos de `data`
 * que el dueño puede cambiar; todo lo demás (type, colorFondo/
 * colorTexto, overlay, el orden y la cantidad de bloques) sale siempre
 * de lo guardado en la base de datos, nunca de lo que mande el
 * navegador — así se cumple la regla aunque alguien manipule el payload
 * a mano.
 */
final class ServicioContenidoEditableDePagina
{
    /**
     * @var array<string, list<string>>
     */
    private const CAMPOS_EDITABLES_POR_TIPO = [
        TipoBloque::BarraPromocion->value => ['texto', 'textoBoton', 'enlaceBoton'],
        TipoBloque::Portada->value => ['titulo', 'horarioHoy', 'descripcion', 'textoBotonPrincipal', 'enlaceVideo', 'imagen', 'textoManuscrito'],
        TipoBloque::Texto->value => ['titulo', 'parrafo'],
        TipoBloque::TextoConImagen->value => ['etiqueta', 'titulo', 'parrafo', 'fraseResaltada', 'textoBoton', 'enlaceBoton', 'imagen', 'textoManuscrito'],
        TipoBloque::Galeria->value => ['fotos'],
        TipoBloque::BannerOferta->value => ['etiqueta', 'titulo', 'descripcion', 'textoBoton', 'enlaceBoton', 'imagen', 'textoManuscrito'],
    ];

    /**
     * Los bloques que tienen texto o fotos editables, con su posición
     * real en el arreglo completo (la misma que espera `actualizar()` al
     * recibir de vuelta los cambios). `$bloques` llega directo de
     * `Pagina::bloques` (columna `array` sin forma fija en el modelo),
     * de ahí las comprobaciones defensivas de tipo.
     *
     * @param  array<int|string, mixed>  $bloques
     * @return list<array{indice: int, tipo: string, tipoEtiqueta: string, data: array<string, mixed>}>
     */
    public function bloquesEditables(array $bloques): array
    {
        $editables = [];

        foreach ($bloques as $indice => $bloque) {
            $tipo = $this->tipoDelBloque($bloque);

            if ($tipo === null || ! array_key_exists($tipo, self::CAMPOS_EDITABLES_POR_TIPO)) {
                continue;
            }

            $editables[] = [
                'indice' => (int) $indice,
                'tipo' => $tipo,
                'tipoEtiqueta' => TipoBloque::from($tipo)->etiqueta(),
                'data' => $this->datosDelBloque($bloque),
            ];
        }

        return $editables;
    }

    /**
     * @param  list<array{indice: int, data: array<string, mixed>}>  $bloquesEnviados
     */
    public function actualizar(Pagina $pagina, array $bloquesEnviados): Pagina
    {
        $pagina->update(['bloques' => $this->sanitizar($pagina->bloques, $bloquesEnviados)]);

        return $pagina;
    }

    /**
     * @param  array<int|string, mixed>  $original
     * @param  list<array{indice: int, data: array<string, mixed>}>  $enviados
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    private function sanitizar(array $original, array $enviados): array
    {
        $datosEnviadosPorIndice = [];

        foreach ($enviados as $bloqueEnviado) {
            $datosEnviadosPorIndice[$bloqueEnviado['indice']] = $bloqueEnviado['data'];
        }

        $bloques = [];

        foreach ($original as $indice => $bloque) {
            $tipo = $this->tipoDelBloque($bloque);

            if ($tipo === null) {
                continue;
            }

            $datos = $this->datosDelBloque($bloque);
            $campos = self::CAMPOS_EDITABLES_POR_TIPO[$tipo] ?? [];
            $datosEnviados = $datosEnviadosPorIndice[$indice] ?? [];

            foreach ($campos as $campo) {
                if (array_key_exists($campo, $datosEnviados)) {
                    $datos[$campo] = $datosEnviados[$campo];
                }
            }

            $bloques[] = ['type' => $tipo, 'data' => $datos];
        }

        return $bloques;
    }

    private function tipoDelBloque(mixed $bloque): ?string
    {
        if (! is_array($bloque) || ! is_string($bloque['type'] ?? null)) {
            return null;
        }

        return $bloque['type'];
    }

    /**
     * @return array<string, mixed>
     */
    private function datosDelBloque(mixed $bloque): array
    {
        if (! is_array($bloque) || ! is_array($bloque['data'] ?? null)) {
            return [];
        }

        return $bloque['data'];
    }
}
