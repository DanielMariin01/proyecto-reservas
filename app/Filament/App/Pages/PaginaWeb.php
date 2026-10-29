<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Actions\PublicarPagina;
use App\Enums\Permiso;
use App\Enums\TipoBloque;
use App\Models\Negocio;
use App\Models\Pagina;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * RF-PAG-01: el dueño edita el texto y las fotos de los bloques que el
 * super admin ya dejó armados (nunca agrega, quita ni reordena bloques,
 * ni cambia colores), y solo si el super admin lo permitió para su
 * negocio. Publica directo sus propios cambios, sin pasar por el super
 * admin.
 */
final class PaginaWeb extends Page
{
    use WithFileUploads;

    private const REGLAS_IMAGEN = ['image', 'max:5120'];

    /**
     * Por tipo de bloque, los únicos campos de `data` que el dueño puede
     * cambiar. Todo lo demás (type, combinacionColor, el orden y la
     * cantidad de bloques) sale siempre de lo guardado en la base de
     * datos, nunca de lo que mande el navegador — así se cumple RF-PAG-01
     * ("nunca agrega, quita ni reordena bloques, ni cambia colores")
     * aunque alguien manipule el payload de Livewire a mano.
     *
     * @var array<string, list<string>>
     */
    private const CAMPOS_EDITABLES_POR_TIPO = [
        TipoBloque::Portada->value => ['titulo', 'subtitulo', 'descripcion', 'imagen'],
        TipoBloque::Texto->value => ['titulo', 'parrafo'],
        TipoBloque::TextoConImagen->value => ['titulo', 'parrafo', 'fraseResaltada', 'imagen'],
        TipoBloque::Galeria->value => ['fotos'],
    ];

    protected static ?string $slug = 'pagina-web';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Mi página web';

    protected string $view = 'filament.app.pages.pagina-web';

    /**
     * Mismo formato crudo que guarda el Builder de Filament ({type, data}),
     * no el {tipo, datos} que expone la API pública — esta página edita
     * directo la columna `paginas.bloques`. Mientras no se guarda, `data.imagen`
     * puede ser un TemporaryUploadedFile en vez de la ruta ya guardada.
     *
     * @var array<int, array{type: string, data: array<string, mixed>}>
     */
    public array $bloques = [];

    /**
     * Fotos nuevas de galería pendientes de subir, por índice de bloque.
     * Aparte de $bloques porque un <input multiple> reemplaza su valor
     * completo en cada selección: así no se pierden las fotos existentes.
     *
     * @var array<int, list<TemporaryUploadedFile>>
     */
    public array $fotosNuevasPorIndice = [];

    public static function canAccess(): bool
    {
        $usuario = Filament::auth()->user();

        if ($usuario === null) {
            return false;
        }

        if ($usuario->es_super_admin) {
            return true;
        }

        return $usuario->can(Permiso::GestionarNegocio->value) && Negocio::actual()->permite_editar_pagina;
    }

    public function mount(): void
    {
        $this->bloques = $this->paginaOFallar()->bloques;
    }

    /**
     * Los índices de $bloques cuyo tipo tiene texto o fotos editables,
     * para que la vista sepa a cuáles ponerles un formulario y a cuáles no.
     *
     * @return list<int>
     */
    public function indicesEditables(): array
    {
        $indices = [];

        foreach ($this->bloques as $indice => $bloque) {
            if (array_key_exists($bloque['type'], self::CAMPOS_EDITABLES_POR_TIPO)) {
                $indices[] = $indice;
            }
        }

        return $indices;
    }

    public function quitarFotoExistente(int $indice, int $posicion): void
    {
        $fotos = $this->bloques[$indice]['data']['fotos'] ?? [];
        unset($fotos[$posicion]);
        $this->bloques[$indice]['data']['fotos'] = array_values($fotos);
    }

    public function guardarYPublicar(): void
    {
        $pagina = $this->paginaOFallar();

        $bloques = $this->sanitizarBloques($pagina->bloques, $this->bloques);
        $bloques = $this->subirImagenesPendientes($bloques);

        if ($bloques === null) {
            return;
        }

        $pagina->update(['bloques' => $bloques]);

        app(PublicarPagina::class)->ejecutar($pagina->refresh(), Filament::auth()->user());

        $this->bloques = $bloques;
        $this->fotosNuevasPorIndice = [];
        Notification::make()->success()->title('Página publicada')->send();
    }

    /**
     * Reconstruye $bloques a partir de lo guardado en la base de datos
     * ($original), tomando del navegador ($enviados) solo los campos que
     * CAMPOS_EDITABLES_POR_TIPO permite para cada tipo. El orden, la
     * cantidad de bloques y cualquier campo no listado (type,
     * combinacionColor…) siempre quedan como estaban.
     *
     * @param  array<int, array{type: string, data: array<string, mixed>}>  $original
     * @param  array<int, array{type?: string, data?: array<string, mixed>}>  $enviados
     * @return array<int, array{type: string, data: array<string, mixed>}>
     */
    private function sanitizarBloques(array $original, array $enviados): array
    {
        $bloques = [];

        foreach ($original as $indice => $bloque) {
            $campos = self::CAMPOS_EDITABLES_POR_TIPO[$bloque['type']] ?? [];
            $datosEnviados = $enviados[$indice]['data'] ?? [];

            foreach ($campos as $campo) {
                if (array_key_exists($campo, $datosEnviados)) {
                    $bloque['data'][$campo] = $datosEnviados[$campo];
                }
            }

            $bloques[$indice] = $bloque;
        }

        return $bloques;
    }

    /**
     * Valida y guarda en el disco `public` cada imagen nueva (la portada
     * o el texto con imagen reemplazados, las fotos nuevas de galería).
     * Si una imagen no pasa la validación, avisa y no guarda nada — para
     * no publicar una página a medio actualizar.
     *
     * @param  array<int, array{type: string, data: array<string, mixed>}>  $bloques
     * @return array<int, array{type: string, data: array<string, mixed>}>|null
     */
    private function subirImagenesPendientes(array $bloques): ?array
    {
        foreach ($bloques as $indice => $bloque) {
            $imagen = $bloque['data']['imagen'] ?? null;

            if ($imagen instanceof TemporaryUploadedFile) {
                $etiqueta = TipoBloque::from($bloque['type'])->etiqueta();

                if (! $this->archivoValido($imagen, "La imagen de {$etiqueta}")) {
                    return null;
                }

                $bloque['data']['imagen'] = $imagen->store('paginas', 'public');
                $bloques[$indice] = $bloque;
            }
        }

        foreach ($this->fotosNuevasPorIndice as $indice => $fotosNuevas) {
            if (($bloques[$indice]['type'] ?? null) !== TipoBloque::Galeria->value) {
                continue;
            }

            $bloque = $bloques[$indice];
            $rutasExistentes = $bloque['data']['fotos'] ?? [];

            foreach ($fotosNuevas as $fotoNueva) {
                if (! $this->archivoValido($fotoNueva, 'Una de las fotos de la galería')) {
                    return null;
                }

                $rutasExistentes[] = $fotoNueva->store('paginas', 'public');
            }

            $bloque['data']['fotos'] = $rutasExistentes;
            $bloques[$indice] = $bloque;
        }

        return $bloques;
    }

    private function archivoValido(TemporaryUploadedFile $archivo, string $etiqueta): bool
    {
        $validador = Validator::make(['archivo' => $archivo], ['archivo' => self::REGLAS_IMAGEN]);

        if ($validador->fails()) {
            Notification::make()->danger()->title("{$etiqueta} no es válida")->body('Debe ser una imagen de máximo 5 MB.')->send();

            return false;
        }

        return true;
    }

    private function paginaOFallar(): Pagina
    {
        $pagina = Negocio::actual()->pagina;

        abort_if($pagina === null, 404);

        return $pagina;
    }
}
