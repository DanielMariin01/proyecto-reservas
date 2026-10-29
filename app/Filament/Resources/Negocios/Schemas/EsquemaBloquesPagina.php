<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Schemas;

use App\Enums\FuenteTestimonios;
use App\Enums\IconoBloque;
use App\Enums\TipoBloque;
use App\Services\CalculadoraContraste;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

/**
 * Los bloques de la skill diseno-reservas (sección 6, plantilla de
 * landing). Equipo, servicios, cursos y productos no tienen campos
 * propios (RF-BLQ-02: no guardan datos, los consultan al sistema al
 * mostrarse) — agregarlos o quitarlos del Builder ya decide si el
 * bloque aparece. Cursos y productos están aquí para que el registro no
 * cambie cuando su Fase 2/3 llegue (RF-BLQ-04), pero React los muestra
 * como "Próximamente" mientras tanto.
 */
final class EsquemaBloquesPagina
{
    private const MINIMO_CONTRASTE_AA = 4.5;

    /**
     * @return list<Block>
     */
    public static function blocks(): array
    {
        return [
            Block::make(TipoBloque::BarraPromocion->value)
                ->label(TipoBloque::BarraPromocion->etiqueta())
                ->schema([
                    TextInput::make('texto')->required()->maxLength(255),
                    TextInput::make('textoBoton')->maxLength(255),
                    TextInput::make('enlaceBoton')->url()->maxLength(255),
                    ...self::camposColorLibre('#673DE6', '#FFFFFF'),
                ]),
            Block::make(TipoBloque::Encabezado->value)
                ->label(TipoBloque::Encabezado->etiqueta())
                ->schema([
                    self::repetidorEnlaces('enlaces', 'Enlaces de navegación', maximo: 5),
                ]),
            Block::make(TipoBloque::Portada->value)
                ->label(TipoBloque::Portada->etiqueta())
                ->schema([
                    TextInput::make('etiqueta')->maxLength(255)->helperText('Palabra o frase corta, va en mayúsculas arriba del título.'),
                    TextInput::make('titulo')->required()->maxLength(255),
                    Textarea::make('descripcion')->rows(3),
                    TextInput::make('textoBotonPrincipal')->default('Reservar cita')->maxLength(255),
                    Toggle::make('mostrarBotonVideo')->label('Mostrar botón "Ver video"')->live()->default(false),
                    TextInput::make('enlaceVideo')
                        ->label('Enlace del video')
                        ->url()
                        ->maxLength(255)
                        ->visible(fn (Get $get): bool => (bool) $get('mostrarBotonVideo')),
                    self::repetidorIconos('iconos', 'Fila de íconos', maximo: 4),
                    FileUpload::make('imagen')->image()->disk('public')->directory('paginas'),
                    TextInput::make('textoManuscrito')
                        ->maxLength(255)
                        ->helperText('Texto decorativo superpuesto en la foto, en Asap itálica (sin fuente nueva).'),
                    ...self::camposColorLibre('#3E4043', '#FFFFFF'),
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
                    ...self::camposColorLibre('#FFFFFF', '#3E4043'),
                ]),
            Block::make(TipoBloque::TextoConImagen->value)
                ->label(TipoBloque::TextoConImagen->etiqueta())
                ->schema([
                    TextInput::make('etiqueta')->maxLength(255),
                    TextInput::make('titulo')->maxLength(255),
                    Textarea::make('parrafo')
                        ->rows(4)
                        ->helperText('Modo simple: se ignora si agregas al menos un motivo abajo.'),
                    TextInput::make('fraseResaltada')
                        ->maxLength(255)
                        ->helperText('Debe aparecer tal cual dentro del párrafo; si no coincide, no se resalta nada.'),
                    self::repetidorMotivos('motivos', 'Motivos ("por qué elegirnos")', maximo: 4),
                    TextInput::make('textoBoton')->maxLength(255),
                    TextInput::make('enlaceBoton')->url()->maxLength(255),
                    FileUpload::make('imagen')->image()->disk('public')->directory('paginas'),
                    TextInput::make('textoManuscrito')->maxLength(255),
                    ...self::camposColorLibre('#FFFFFF', '#3E4043'),
                ]),
            Block::make(TipoBloque::Galeria->value)
                ->label(TipoBloque::Galeria->etiqueta())
                ->schema([
                    FileUpload::make('fotos')->image()->multiple()->disk('public')->directory('paginas'),
                ]),
            Block::make(TipoBloque::BannerOferta->value)
                ->label(TipoBloque::BannerOferta->etiqueta())
                ->schema([
                    TextInput::make('etiqueta')->maxLength(255),
                    TextInput::make('titulo')->required()->maxLength(255),
                    Textarea::make('descripcion')->rows(3),
                    TextInput::make('textoBoton')->maxLength(255),
                    TextInput::make('enlaceBoton')->url()->maxLength(255),
                    FileUpload::make('imagen')->image()->disk('public')->directory('paginas'),
                    TextInput::make('textoManuscrito')->maxLength(255),
                    ...self::camposColorLibre('#673DE6', '#FFFFFF'),
                ]),
            Block::make(TipoBloque::Testimonios->value)
                ->label(TipoBloque::Testimonios->etiqueta())
                ->schema([
                    Hidden::make('fuente')->default(FuenteTestimonios::Manual->value),
                    Repeater::make('testimonios')
                        ->label('Testimonios')
                        ->schema([
                            Textarea::make('cita')->required()->rows(3)->maxLength(500),
                            TextInput::make('nombre')->required()->maxLength(255),
                            FileUpload::make('foto')->image()->disk('public')->directory('paginas'),
                            Select::make('estrellas')
                                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                                ->default(5)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Agregar testimonio')
                        ->maxItems(6)
                        ->defaultItems(0),
                    ...self::camposColorLibre('#FFFFFF', '#3E4043'),
                ]),
            Block::make(TipoBloque::Ubicacion->value)
                ->label(TipoBloque::Ubicacion->etiqueta()),
            Block::make(TipoBloque::Whatsapp->value)
                ->label(TipoBloque::Whatsapp->etiqueta()),
            Block::make(TipoBloque::PieDePagina->value)
                ->label(TipoBloque::PieDePagina->etiqueta())
                ->schema([
                    TextInput::make('descripcionCorta')->label('Descripción corta')->maxLength(255),
                    self::repetidorEnlaces('enlaces', 'Enlaces', maximo: 5),
                ]),
        ];
    }

    /**
     * 2 `ColorPicker` libres (sin restricción de contraste obligatoria,
     * a diferencia de las paletas de marca): colorTexto muestra una
     * advertencia no bloqueante cuando el contraste con colorFondo es
     * bajo (skill diseno-reservas, sección 1: excepción documentada).
     *
     * @return array{0: ColorPicker, 1: ColorPicker}
     */
    private static function camposColorLibre(string $colorFondoPorDefecto, string $colorTextoPorDefecto): array
    {
        return [
            ColorPicker::make('colorFondo')
                ->label('Color de fondo')
                ->default($colorFondoPorDefecto)
                ->live(),
            ColorPicker::make('colorTexto')
                ->label('Color de texto')
                ->default($colorTextoPorDefecto)
                ->live()
                ->hintIcon(fn (Get $get): ?Heroicon => self::advertenciaContraste($get) !== null ? Heroicon::OutlinedExclamationTriangle : null)
                ->hintColor('warning')
                ->hint(fn (Get $get): ?string => self::advertenciaContraste($get)),
        ];
    }

    private static function advertenciaContraste(Get $get): ?string
    {
        $fondo = $get('colorFondo');
        $texto = $get('colorTexto');

        if (! is_string($fondo) || ! is_string($texto) || $fondo === '' || $texto === '') {
            return null;
        }

        $contraste = app(CalculadoraContraste::class)->contrasteEntre($fondo, $texto);

        if ($contraste >= self::MINIMO_CONTRASTE_AA) {
            return null;
        }

        return sprintf('Este texto puede ser difícil de leer con este fondo (contraste %.1f:1; se recomienda 4,5:1 o más). Se puede guardar igual.', $contraste);
    }

    private static function selectorIcono(): Select
    {
        $opciones = [];

        foreach (IconoBloque::cases() as $icono) {
            $opciones[$icono->value] = $icono->etiqueta();
        }

        return Select::make('icono')->label('Ícono')->options($opciones)->required();
    }

    private static function repetidorIconos(string $campo, string $etiqueta, int $maximo): Repeater
    {
        return Repeater::make($campo)
            ->label($etiqueta)
            ->schema([
                self::selectorIcono(),
                TextInput::make('etiqueta')->label('Etiqueta corta')->required()->maxLength(60),
            ])
            ->columns(2)
            ->addActionLabel('Agregar ícono')
            ->maxItems($maximo)
            ->defaultItems(0);
    }

    private static function repetidorMotivos(string $campo, string $etiqueta, int $maximo): Repeater
    {
        return Repeater::make($campo)
            ->label($etiqueta)
            ->schema([
                self::selectorIcono(),
                TextInput::make('titulo')->label('Título corto')->required()->maxLength(60),
                Textarea::make('descripcion')->rows(2)->maxLength(255),
            ])
            ->columns(3)
            ->addActionLabel('Agregar motivo')
            ->maxItems($maximo)
            ->defaultItems(0);
    }

    private static function repetidorEnlaces(string $campo, string $etiqueta, int $maximo): Repeater
    {
        return Repeater::make($campo)
            ->label($etiqueta)
            ->schema([
                TextInput::make('texto')->required()->maxLength(60),
                TextInput::make('url')->required()->maxLength(255),
            ])
            ->columns(2)
            ->addActionLabel('Agregar enlace')
            ->maxItems($maximo)
            ->defaultItems(0);
    }
}
