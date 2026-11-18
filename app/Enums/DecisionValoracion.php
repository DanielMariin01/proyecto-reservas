<?php

declare(strict_types=1);

namespace App\Enums;

enum DecisionValoracion: string
{
    case SeRealiza = 'se_realiza';
    case SeModifica = 'se_modifica';
    case SeAplaza = 'se_aplaza';

    public function etiqueta(): string
    {
        return match ($this) {
            self::SeRealiza => 'Se realiza',
            self::SeModifica => 'Se modifica',
            self::SeAplaza => 'Se aplaza/no se realiza',
        };
    }
}
