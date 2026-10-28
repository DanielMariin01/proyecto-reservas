<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Citas;

use App\Filament\App\Resources\Citas\Pages\CreateCita;
use App\Filament\App\Resources\Citas\Pages\ListCitas;
use App\Filament\App\Resources\Citas\Schemas\CitaForm;
use App\Filament\App\Resources\Citas\Tables\CitasTable;
use App\Models\Cita;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CitaResource extends Resource
{
    protected static ?string $model = Cita::class;

    protected static ?string $slug = 'citas';

    protected static string|UnitEnum|null $navigationGroup = 'Reservas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    public static function form(Schema $schema): Schema
    {
        return CitaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CitasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCitas::route('/'),
            'create' => CreateCita::route('/create'),
        ];
    }
}
