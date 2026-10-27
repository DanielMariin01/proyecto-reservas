<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales;

use App\Filament\App\Resources\Profesionales\Pages\CreateProfesional;
use App\Filament\App\Resources\Profesionales\Pages\EditProfesional;
use App\Filament\App\Resources\Profesionales\Pages\ListProfesionales;
use App\Filament\App\Resources\Profesionales\Pages\ViewProfesional;
use App\Filament\App\Resources\Profesionales\RelationManagers\ServiciosRelationManager;
use App\Filament\App\Resources\Profesionales\Schemas\ProfesionalForm;
use App\Filament\App\Resources\Profesionales\Schemas\ProfesionalInfolist;
use App\Filament\App\Resources\Profesionales\Tables\ProfesionalesTable;
use App\Models\Profesional;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProfesionalResource extends Resource
{
    protected static ?string $model = Profesional::class;

    // El pluralizador de Filament no conoce español ("Profesional" -> "Profesionals"),
    // así que sin esto el slug por defecto queda mal formado (duplica el segmento).
    protected static ?string $slug = 'profesionales';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function form(Schema $schema): Schema
    {
        return ProfesionalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProfesionalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfesionalesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ServiciosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfesionales::route('/'),
            'create' => CreateProfesional::route('/create'),
            'view' => ViewProfesional::route('/{record}'),
            'edit' => EditProfesional::route('/{record}/edit'),
        ];
    }
}
