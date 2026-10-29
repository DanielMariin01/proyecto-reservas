<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Schemas;

use App\Enums\TipoBloque;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
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
                    TextInput::make('subtitulo')->maxLength(255),
                    FileUpload::make('imagen')->image()->disk('public')->directory('paginas'),
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
}
