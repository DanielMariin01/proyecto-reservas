<?php

declare(strict_types=1);

namespace App\Filament\Resources\RegistroAuditorias;

use App\Filament\Resources\RegistroAuditorias\Pages\ListRegistroAuditorias;
use App\Filament\Resources\RegistroAuditorias\Tables\RegistroAuditoriasTable;
use App\Models\RegistroAuditoria;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RegistroAuditoriaResource extends Resource
{
    protected static ?string $model = RegistroAuditoria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Auditoría';

    public static function table(Table $table): Table
    {
        return RegistroAuditoriasTable::configure($table);
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
            'index' => ListRegistroAuditorias::route('/'),
        ];
    }
}
