<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Usuarios;

use App\Filament\App\Resources\Usuarios\Pages\CreateUsuario;
use App\Filament\App\Resources\Usuarios\Pages\EditUsuario;
use App\Filament\App\Resources\Usuarios\Pages\ListUsuarios;
use App\Filament\App\Resources\Usuarios\Schemas\UsuarioForm;
use App\Filament\App\Resources\Usuarios\Tables\UsuariosTable;
use App\Models\Negocio;
use App\Models\Usuario;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsuarioResource extends Resource
{
    protected static ?string $model = Usuario::class;

    protected static ?string $navigationLabel = 'Usuarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return UsuarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsuarios::route('/'),
            'create' => CreateUsuario::route('/create'),
            'edit' => EditUsuario::route('/{record}/edit'),
        ];
    }

    /**
     * Usuario no tiene una relacion directa "negocio" (pertenece a varios
     * negocios via roles), asi que el filtrado automatico de Filament por
     * tenant no aplica aqui: se hace a mano en getEloquentQuery().
     */
    public static function isScopedToTenant(): bool
    {
        return false;
    }

    /**
     * @return Builder<Usuario>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Usuario> $query */
        $query = parent::getEloquentQuery();

        return $query->deNegocio(Negocio::actual()->id);
    }
}
