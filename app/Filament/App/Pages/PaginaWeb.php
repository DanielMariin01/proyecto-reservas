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
        $tiposEditables = [TipoBloque::Portada->value, TipoBloque::Texto->value, TipoBloque::Galeria->value];

        $indices = [];

        foreach ($this->bloques as $indice => $bloque) {
            if (in_array($bloque['type'], $tiposEditables, true)) {
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
        if (! $this->subirImagenesPendientes()) {
            return;
        }

        $pagina = $this->paginaOFallar();
        $pagina->update(['bloques' => $this->bloques]);

        app(PublicarPagina::class)->ejecutar($pagina->refresh(), Filament::auth()->user());

        $this->fotosNuevasPorIndice = [];
        Notification::make()->success()->title('Página publicada')->send();
    }

    /**
     * Valida y guarda en el disco `public` cada imagen nueva (la portada
     * reemplazada, las fotos nuevas de galería), antes de persistir
     * $bloques. Si una imagen no pasa la validación, avisa y no guarda
     * nada — para no publicar una página a medio actualizar.
     */
    private function subirImagenesPendientes(): bool
    {
        $bloques = $this->bloques;

        foreach ($bloques as $indice => $bloque) {
            $imagen = $bloque['data']['imagen'] ?? null;

            if ($imagen instanceof TemporaryUploadedFile) {
                if (! $this->archivoValido($imagen, 'La imagen de portada')) {
                    return false;
                }

                $bloque['data']['imagen'] = $imagen->store('paginas', 'public');
                $bloques[$indice] = $bloque;
            }
        }

        foreach ($this->fotosNuevasPorIndice as $indice => $fotosNuevas) {
            $bloque = $bloques[$indice];
            $rutasExistentes = $bloque['data']['fotos'] ?? [];

            foreach ($fotosNuevas as $fotoNueva) {
                if (! $this->archivoValido($fotoNueva, 'Una de las fotos de la galería')) {
                    return false;
                }

                $rutasExistentes[] = $fotoNueva->store('paginas', 'public');
            }

            $bloque['data']['fotos'] = $rutasExistentes;
            $bloques[$indice] = $bloque;
        }

        $this->bloques = $bloques;

        return true;
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
