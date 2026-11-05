<?php

declare(strict_types=1);

namespace App\Filament\Resources\RegistroErrores;

use App\Filament\Resources\RegistroErrores\Pages\ListRegistroErrores;
use App\Filament\Resources\RegistroErrores\Tables\RegistroErroresTable;
use App\Models\RegistroError;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RegistroErrorResource extends Resource
{
    protected static ?string $model = RegistroError::class;

    /**
     * Filament deriva el slug por defecto pluralizando el nombre de la
     * clase en inglés ("RegistroErrors"), que no coincide con la carpeta
     * en español y produce una URL con el segmento duplicado
     * (/registro-errores/registro-errors). Se fija a mano para evitarlo.
     */
    protected static ?string $slug = 'registro-errores';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationCircle;

    protected static ?string $navigationLabel = 'Errores';

    protected static string|UnitEnum|null $navigationGroup = 'Observabilidad';

    public static function table(Table $table): Table
    {
        return RegistroErroresTable::configure($table);
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
            'index' => ListRegistroErrores::route('/'),
        ];
    }
}
