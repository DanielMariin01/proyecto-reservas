<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Pages;

use App\Actions\PublicarPagina;
use App\Filament\Resources\Negocios\NegocioResource;
use App\Models\Negocio;
use App\Models\Pagina;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditNegocio extends EditRecord
{
    protected static string $resource = NegocioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            $this->accionVistaPrevia(),
            $this->accionPublicarPagina(),
            DeleteAction::make(),
        ];
    }

    private function accionVistaPrevia(): Action
    {
        /** @var Negocio $negocio */
        $negocio = $this->getRecord();

        return Action::make('vistaPreviaPagina')
            ->label('Vista previa de la página')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->url(url("/{$negocio->slug}?vista_previa=1"))
            ->openUrlInNewTab();
    }

    private function accionPublicarPagina(): Action
    {
        return Action::make('publicarPagina')
            ->label('Publicar página')
            ->icon(Heroicon::OutlinedRocketLaunch)
            ->color('success')
            ->requiresConfirmation()
            ->action(function (): void {
                /** @var Negocio $negocio */
                $negocio = $this->getRecord();
                $pagina = Pagina::query()->where('negocio_id', $negocio->id)->first();

                if ($pagina === null) {
                    Notification::make()->danger()->title('Guarda la página antes de publicarla')->send();

                    return;
                }

                app(PublicarPagina::class)->ejecutar($pagina, Filament::auth()->user());
                Notification::make()->success()->title('Página publicada')->send();
            });
    }
}
