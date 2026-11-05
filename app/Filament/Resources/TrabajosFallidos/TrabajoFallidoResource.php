<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrabajosFallidos;

use App\Filament\Resources\TrabajosFallidos\Pages\ListTrabajosFallidos;
use App\Filament\Resources\TrabajosFallidos\Tables\TrabajosFallidosTable;
use App\Models\TrabajoFallido;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TrabajoFallidoResource extends Resource
{
    protected static ?string $model = TrabajoFallido::class;

    /**
     * Mismo caso que RegistroErrorResource: el plural en inglés de
     * "TrabajoFallido" no coincide con la carpeta en español y duplica
     * el segmento de la URL si se deja el slug por defecto.
     */
    protected static ?string $slug = 'trabajos-fallidos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Trabajos fallidos';

    protected static string|UnitEnum|null $navigationGroup = 'Observabilidad';

    public static function table(Table $table): Table
    {
        return TrabajosFallidosTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrabajosFallidos::route('/'),
        ];
    }
}
