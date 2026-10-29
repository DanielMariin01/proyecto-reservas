<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Schemas;

use App\Enums\CombinacionColorBloque;
use App\Enums\TipoBloque;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Los bloques de la skill diseno-reservas (sección 6). Equipo, servicios,
 * cursos y productos no tienen campos propios (RF-BLQ-02: no guardan
 * datos, los consultan al sistema al mostrarse) — agregarlos o quitarlos
 * del Builder ya decide si el bloque aparece. Cursos y productos están
 * aquí para que el registro no cambie cuando su Fase 2/3 llegue
 * (RF-BLQ-04), pero React los muestra como "Próximamente" mientras tanto.
 */
final class EsquemaBloquesPagina
{
    /**
     * @return list<Block>
     */
    public static function blocks(): array
    {
        return [
            Block::make(TipoBloque::Portada->value)
                ->label(TipoBloque::Portada->etiqueta())
                ->schema([
                    TextInput::make('titulo')->required()->maxLength(255),
                    TextInput::make('subtitulo')->maxLength(255)->helperText('Frase corta de acento, no el párrafo de descripción.'),
                    Textarea::make('descripcion')
                        ->rows(3)
                        ->helperText('Párrafo corto debajo del subtítulo.'),
                    FileUpload::make('imagen')->image()->disk('public')->directory('paginas'),
                    self::selectorCombinacionColor(CombinacionColorBloque::Oscura),
                ]),
            Block::make(TipoBloque::Equipo->value)
                ->label(TipoBloque::Equipo->etiqueta()),
            Block::make(TipoBloque::Servicios->value)
                ->label(TipoBloque::Servicios->etiqueta()),
            Block::make(TipoBloque::Cursos->value)
                ->label(TipoBloque::Cursos->etiqueta().' (próximamente)'),
            Block::make(TipoBloque::Productos->value)
                ->label(TipoBloque::Productos->etiqueta().' (próximamente)'),
            Block::make(TipoBloque::Texto->value)
                ->label(TipoBloque::Texto->etiqueta())
                ->schema([
                    TextInput::make('titulo')->maxLength(255),
                    Textarea::make('parrafo')
                        ->rows(4)
                        ->helperText('Máximo 70 caracteres por línea para que se lea bien.'),
                    self::selectorCombinacionColor(CombinacionColorBloque::Clara),
                ]),
            Block::make(TipoBloque::TextoConImagen->value)
                ->label(TipoBloque::TextoConImagen->etiqueta())
                ->schema([
                    TextInput::make('titulo')->maxLength(255),
                    Textarea::make('parrafo')
                        ->rows(4)
                        ->helperText('Máximo 70 caracteres por línea para que se lea bien.'),
                    TextInput::make('fraseResaltada')
                        ->maxLength(255)
                        ->helperText('Debe aparecer tal cual dentro del párrafo; si no coincide, no se resalta nada.'),
                    FileUpload::make('imagen')->image()->disk('public')->directory('paginas'),
                    self::selectorCombinacionColor(CombinacionColorBloque::Clara),
                ]),
            Block::make(TipoBloque::Galeria->value)
                ->label(TipoBloque::Galeria->etiqueta())
                ->schema([
                    FileUpload::make('fotos')->image()->multiple()->disk('public')->directory('paginas'),
                ]),
            Block::make(TipoBloque::Ubicacion->value)
                ->label(TipoBloque::Ubicacion->etiqueta()),
            Block::make(TipoBloque::Whatsapp->value)
                ->label(TipoBloque::Whatsapp->etiqueta()),
        ];
    }

    /**
     * Solo las 3 combinaciones de fondo/texto validadas en AA (sección 1
     * de la skill diseno-reservas) — nunca un color suelto, y nunca algo
     * que el dueño pueda tocar desde su editor limitado.
     */
    private static function selectorCombinacionColor(CombinacionColorBloque $porDefecto): Select
    {
        $opciones = [];

        foreach (CombinacionColorBloque::cases() as $combinacion) {
            $opciones[$combinacion->value] = $combinacion->etiqueta();
        }

        return Select::make('combinacionColor')
            ->label('Combinación de color')
            ->options($opciones)
            ->default($porDefecto->value);
    }
}
