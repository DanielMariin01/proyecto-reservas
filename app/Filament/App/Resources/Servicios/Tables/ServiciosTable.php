<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Servicios\Tables;

use App\Support\Dinero;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiciosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('orden')
            ->defaultSort('orden')
            ->columns([
                ImageColumn::make('foto')->disk('public'),
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('categoriaServicio.nombre')->label('Categoría')->default('Sin categoría'),
                TextColumn::make('duracion_minutos')->label('Duración')->suffix(' min'),
                TextColumn::make('precio')->formatStateUsing(fn (int $state): string => Dinero::formatearPesos($state)),
                IconColumn::make('visible')->boolean(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
