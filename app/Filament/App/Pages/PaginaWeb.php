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

/**
 * RF-PAG-01: el dueño solo edita los textos de los bloques que el super
 * admin ya dejó armados (nunca agrega, quita ni reordena bloques, ni
 * cambia colores), y solo si el super admin lo permitió para su negocio.
 * Publica directo sus propios cambios (sin pasar por el super admin).
 *
 * Las fotos (portada, galería) se quedan por ahora solo en el editor del
 * super admin: subir archivos desde una página Livewire simple sin el
 * componente FileUpload de Filament queda para una siguiente iteración.
 */
final class PaginaWeb extends Page
{
    protected static ?string $slug = 'pagina-web';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Mi página web';

    protected string $view = 'filament.app.pages.pagina-web';

    /**
     * Mismo formato crudo que guarda el Builder de Filament ({type, data}),
     * no el {tipo, datos} que expone la API pública — esta página edita
     * directo la columna `paginas.bloques`.
     *
     * @var array<int, array{type: string, data: array<string, mixed>}>
     */
    public array $bloques = [];

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
     * Los índices de $bloques cuyo tipo tiene texto editable, para que la
     * vista sepa a cuáles ponerles un formulario y a cuáles no.
     *
     * @return list<int>
     */
    public function indicesEditables(): array
    {
        $tiposConTexto = [TipoBloque::Portada->value, TipoBloque::Texto->value];

        $indices = [];

        foreach ($this->bloques as $indice => $bloque) {
            if (in_array($bloque['type'], $tiposConTexto, true)) {
                $indices[] = $indice;
            }
        }

        return $indices;
    }

    public function guardarYPublicar(): void
    {
        $pagina = $this->paginaOFallar();
        $pagina->update(['bloques' => $this->bloques]);

        app(PublicarPagina::class)->ejecutar($pagina->refresh(), Filament::auth()->user());

        Notification::make()->success()->title('Página publicada')->send();
    }

    private function paginaOFallar(): Pagina
    {
        $pagina = Negocio::actual()->pagina;

        abort_if($pagina === null, 404);

        return $pagina;
    }
}
