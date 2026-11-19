<?php

declare(strict_types=1);

namespace App\Services;

use App\Contratos\ServicioFirmaDigital;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class ServicioFirmaDigitalDisco implements ServicioFirmaDigital
{
    private const DISCO = 'local';

    public function guardar(string $imagenBase64, string $carpeta): string
    {
        [$contenido, $extension] = $this->decodificar($imagenBase64);

        $ruta = sprintf('%s/%s.%s', $carpeta, (string) Str::ulid(), $extension);

        Storage::disk(self::DISCO)->put($ruta, $contenido);

        return $ruta;
    }

    public function eliminar(?string $ruta): void
    {
        if ($ruta !== null) {
            Storage::disk(self::DISCO)->delete($ruta);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function decodificar(string $imagenBase64): array
    {
        if (! preg_match('/^data:image\/(png|jpeg);base64,(.+)$/', $imagenBase64, $coincidencias)) {
            throw new InvalidArgumentException('La firma debe venir como una imagen PNG o JPEG en base64.');
        }

        $contenido = base64_decode($coincidencias[2], true);

        if ($contenido === false) {
            throw new InvalidArgumentException('La firma no se pudo decodificar.');
        }

        return [$contenido, $coincidencias[1] === 'jpeg' ? 'jpg' : 'png'];
    }
}
