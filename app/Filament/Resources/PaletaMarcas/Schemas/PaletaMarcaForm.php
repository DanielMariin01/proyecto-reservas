<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaletaMarcas\Schemas;

use App\Services\CalculadoraContraste;
use Closure;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaletaMarcaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->helperText('Ej: "Color Barbería", "Color Uñas". La ve el super admin al elegir la paleta de un negocio.'),
            ColorPicker::make('color_primario')
                ->label('Color primario')
                ->required()
                ->rules([self::reglaContrasteAaConBlanco()])
                ->helperText('Fondo de la combinación "marca" de los bloques: debe cumplir contraste AA (4,5:1) con texto blanco encima.'),
            ColorPicker::make('color_secundario')
                ->label('Color secundario')
                ->required()
                ->helperText('Color de acento del negocio (igual trato que --secundario del sistema: solo texto blanco grande y en negrita).'),
        ]);
    }

    /**
     * Filament evalúa con su propia inyección cualquier closure que vea
     * en ->rules(), así que esta debe ser una closure sin parámetros que
     * devuelve la closure de validación real de Laravel (attribute,
     * value, fail) — no la closure de validación directamente.
     */
    private static function reglaContrasteAaConBlanco(): Closure
    {
        return fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || ! app(CalculadoraContraste::class)->cumpleAaConBlanco($value)) {
                $fail('Este color no pasa el contraste AA (mínimo 4,5:1) con texto blanco encima — prueba uno más oscuro.');
            }
        };
    }
}
