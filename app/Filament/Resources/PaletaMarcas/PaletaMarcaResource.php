<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaletaMarcas;

use App\Filament\Resources\PaletaMarcas\Pages\CreatePaletaMarca;
use App\Filament\Resources\PaletaMarcas\Pages\EditPaletaMarca;
use App\Filament\Resources\PaletaMarcas\Pages\ListPaletasMarca;
use App\Filament\Resources\PaletaMarcas\Schemas\PaletaMarcaForm;
use App\Filament\Resources\PaletaMarcas\Tables\PaletasMarcaTable;
use App\Models\PaletaMarca;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaletaMarcaResource extends Resource
{
    protected static ?string $model = PaletaMarca::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?string $navigationLabel = 'Paletas de marca';

    protected static ?string $modelLabel = 'paleta de marca';

    public static function form(Schema $schema): Schema
    {
        return PaletaMarcaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaletasMarcaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaletasMarca::route('/'),
            'create' => CreatePaletaMarca::route('/create'),
            'edit' => EditPaletaMarca::route('/{record}/edit'),
        ];
    }
}
