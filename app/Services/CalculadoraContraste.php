<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Contraste AA (WCAG 2.1, formula de luminancia relativa), para exigir
 * antes de guardar lo que pide la skill diseno-reservas: una paleta de
 * marca nueva o una combinacion de color de bloque.
 */
final class CalculadoraContraste
{
    private const UMBRAL_CANAL_LINEAL = 0.03928;

    public function cumpleAaConBlanco(string $colorHex, float $minimo = 4.5): bool
    {
        return $this->contrasteConBlanco($colorHex) >= $minimo;
    }

    public function contrasteConBlanco(string $colorHex): float
    {
        return $this->contrasteEntre($colorHex, '#FFFFFF');
    }

    public function contrasteEntre(string $colorHexA, string $colorHexB): float
    {
        $masClara = max($this->luminanciaRelativa($colorHexA), $this->luminanciaRelativa($colorHexB));
        $masOscura = min($this->luminanciaRelativa($colorHexA), $this->luminanciaRelativa($colorHexB));

        return ($masClara + 0.05) / ($masOscura + 0.05);
    }

    private function luminanciaRelativa(string $colorHex): float
    {
        [$rojo, $verde, $azul] = $this->canalesRgb($colorHex);

        return 0.2126 * $this->linealizarCanal($rojo)
            + 0.7152 * $this->linealizarCanal($verde)
            + 0.0722 * $this->linealizarCanal($azul);
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private function canalesRgb(string $colorHex): array
    {
        $hex = ltrim($colorHex, '#');

        return [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];
    }

    private function linealizarCanal(float $canal): float
    {
        if ($canal <= self::UMBRAL_CANAL_LINEAL) {
            return $canal / 12.92;
        }

        return (($canal + 0.055) / 1.055) ** 2.4;
    }
}
