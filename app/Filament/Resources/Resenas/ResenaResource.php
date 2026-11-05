<?php

declare(strict_types=1);

namespace App\Filament\Resources\Resenas;

use App\Filament\Resources\Resenas\Pages\ListResenas;
use App\Filament\Resources\Resenas\Tables\ResenasTable;
use App\Models\Resena;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ResenaResource extends Resource
{
    protected static ?string $model = Resena::class;

    protected static ?string $slug = 'resenas';

    protected static string|UnitEnum|null $navigationGroup = 'Observabilidad';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $navigationLabel = 'Reseñas';

    protected static ?string $modelLabel = 'reseña';

    protected static ?string $pluralModelLabel = 'reseñas';

    public static function table(Table $table): Table
    {
        return ResenasTable::configure($table);
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
            'index' => ListResenas::route('/'),
        ];
    }
}
