<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios;

use App\Filament\Resources\Negocios\Pages\CreateNegocio;
use App\Filament\Resources\Negocios\Pages\EditNegocio;
use App\Filament\Resources\Negocios\Pages\ListNegocios;
use App\Filament\Resources\Negocios\Pages\ViewNegocio;
use App\Filament\Resources\Negocios\RelationManagers\PagosSuscripcionRelationManager;
use App\Filament\Resources\Negocios\RelationManagers\RegistrosAuditoriaRelationManager;
use App\Filament\Resources\Negocios\Schemas\NegocioForm;
use App\Filament\Resources\Negocios\Schemas\NegocioInfolist;
use App\Filament\Resources\Negocios\Tables\NegociosTable;
use App\Models\Negocio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NegocioResource extends Resource
{
    protected static ?string $model = Negocio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return NegocioForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NegocioInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NegociosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PagosSuscripcionRelationManager::class,
            RegistrosAuditoriaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNegocios::route('/'),
            'create' => CreateNegocio::route('/create'),
            'view' => ViewNegocio::route('/{record}'),
            'edit' => EditNegocio::route('/{record}/edit'),
        ];
    }
}
