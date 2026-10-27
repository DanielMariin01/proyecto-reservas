<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CategoriaServicios;

use App\Filament\App\Resources\CategoriaServicios\Pages\CreateCategoriaServicio;
use App\Filament\App\Resources\CategoriaServicios\Pages\EditCategoriaServicio;
use App\Filament\App\Resources\CategoriaServicios\Pages\ListCategoriaServicios;
use App\Filament\App\Resources\CategoriaServicios\Schemas\CategoriaServicioForm;
use App\Filament\App\Resources\CategoriaServicios\Tables\CategoriaServiciosTable;
use App\Models\CategoriaServicio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CategoriaServicioResource extends Resource
{
    protected static ?string $model = CategoriaServicio::class;

    protected static ?string $slug = 'categorias-servicio';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function form(Schema $schema): Schema
    {
        return CategoriaServicioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriaServiciosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoriaServicios::route('/'),
            'create' => CreateCategoriaServicio::route('/create'),
            'edit' => EditCategoriaServicio::route('/{record}/edit'),
        ];
    }
}
