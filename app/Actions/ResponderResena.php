<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\ResenaRespondida;
use App\Models\Resena;
use App\Models\Usuario;
use Carbon\CarbonImmutable;

final class ResponderResena
{
    public function ejecutar(Resena $resena, string $respuesta, Usuario $respondidoPor): Resena
    {
        $resena->update([
            'respuesta_texto' => $respuesta,
            'respondida_en' => CarbonImmutable::now(),
        ]);

        ResenaRespondida::dispatch($resena, $respondidoPor);

        return $resena;
    }
}
