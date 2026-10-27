<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Usuarios\Pages;

use App\Filament\App\Resources\Usuarios\UsuarioResource;
use App\Models\Negocio;
use App\Models\Usuario;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EditUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Usuario $usuario */
        $usuario = $this->getRecord();

        $negocio = Negocio::actual();

        app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);

        /** @var Role|null $rol */
        $rol = $usuario->roles()->first();

        $data['rol'] = $rol?->name;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Usuario $record */
        $negocio = Negocio::actual();

        $rol = $data['rol'] ?? null;
        unset($data['rol']);

        $record->update($data);

        if ($rol !== null) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);
            $record->syncRoles([$rol]);
        }

        return $record;
    }
}
