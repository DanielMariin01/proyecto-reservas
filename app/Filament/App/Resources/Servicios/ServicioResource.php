<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Servicios;

use App\Filament\App\Resources\Servicios\Pages\CreateServicio;
use App\Filament\App\Resources\Servicios\Pages\EditServicio;
use App\Filament\App\Resources\Servicios\Pages\ListServicios;
use App\Filament\App\Resources\Servicios\Schemas\ServicioForm;
use App\Filament\App\Resources\Servicios\Tables\ServiciosTable;
use App\Models\Servicio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ServicioResource extends Resource
{
    protected static ?string $model = Servicio::class;

    protected static ?string $slug = 'servicios';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScissors;

    public static function form(Schema $schema): Schema
    {
        return ServicioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiciosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicios::route('/'),
            'create' => CreateServicio::route('/create'),
            'edit' => EditServicio::route('/{record}/edit'),
        ];
    }
}
