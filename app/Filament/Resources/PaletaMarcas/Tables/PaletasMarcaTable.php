<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaletaMarcas\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaletasMarcaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                ColorColumn::make('color_primario')->label('Primario'),
                ColorColumn::make('color_secundario')->label('Secundario'),
                TextColumn::make('negocios_count')->label('Negocios')->counts('negocios'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([]);
    }
}
