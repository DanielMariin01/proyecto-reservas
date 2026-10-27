<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuarioResource;
use App\Models\Usuario;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateUsuario extends CreateRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['password'] = Hash::make(Str::random(32));

        /** @var Usuario $usuario */
        $usuario = Usuario::create($data);

        Password::sendResetLink(['email' => $usuario->email]);

        return $usuario;
    }
}
