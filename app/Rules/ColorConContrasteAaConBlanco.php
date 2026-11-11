<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\CalculadoraContraste;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Misma regla que ya aplicaba PaletaMarcaForm en Filament (sección 1 de
 * la skill diseno-reservas): color_primario debe cumplir AA (4,5:1) con
 * texto blanco encima para poder guardarse.
 */
final class ColorConContrasteAaConBlanco implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! app(CalculadoraContraste::class)->cumpleAaConBlanco($value)) {
            $fail('Este color no pasa el contraste AA (mínimo 4,5:1) con texto blanco encima — prueba uno más oscuro.');
        }
    }
}
