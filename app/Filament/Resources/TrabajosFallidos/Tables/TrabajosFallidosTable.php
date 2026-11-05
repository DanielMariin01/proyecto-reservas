<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrabajosFallidos\Tables;

use App\Models\TrabajoFallido;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class TrabajosFallidosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('failed_at', 'desc')
            ->columns([
                TextColumn::make('failed_at')->label('Falló')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('queue')->label('Cola'),
                TextColumn::make('tipo')->label('Tipo')->badge(),
                TextColumn::make('exception')->label('Excepción')->limit(80)->wrap(),
            ])
            ->filters([])
            ->recordActions([
                Action::make('reintentar')
                    ->label('Reintentar')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (TrabajoFallido $record): void {
                        Artisan::call('queue:retry', ['id' => [$record->uuid]]);

                        Notification::make()->success()->title('El trabajo vuelve a la cola')->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
