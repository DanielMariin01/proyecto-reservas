<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\TipoBloque;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Cadena de reglas: cada tipo de bloque de la pagina (sección 6 de la
 * skill diseno-reservas) exige como maximo un campo propio — el mismo
 * conjunto que ya marcaba ->required() en el Builder de Filament. El
 * resto de campos de cada bloque son opcionales a propósito.
 */
final class BloqueValidoPagina implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || ! isset($value['type'])) {
            $fail('El bloque no tiene un tipo válido.');

            return;
        }

        $tipo = TipoBloque::tryFrom((string) $value['type']);

        if ($tipo === null) {
            $fail('El bloque tiene un tipo desconocido.');

            return;
        }

        $campoRequerido = $this->campoRequeridoPara($tipo);

        if ($campoRequerido === null) {
            return;
        }

        $datos = is_array($value['data'] ?? null) ? $value['data'] : [];
        $valor = $datos[$campoRequerido] ?? null;

        if (! is_string($valor) || trim($valor) === '') {
            $fail("El bloque «{$tipo->etiqueta()}» necesita el campo «{$campoRequerido}».");
        }
    }

    private function campoRequeridoPara(TipoBloque $tipo): ?string
    {
        return match ($tipo) {
            TipoBloque::BarraPromocion => 'texto',
            TipoBloque::Portada, TipoBloque::BannerOferta => 'titulo',
            default => null,
        };
    }
}
