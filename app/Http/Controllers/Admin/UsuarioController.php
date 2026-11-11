<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\RestablecerContrasenaUsuario;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SolicitudUsuario;
use App\Http\Resources\Admin\RecursoUsuario;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class UsuarioController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $usuarios = Usuario::query()->orderBy('nombre')->paginate(25);

        return RecursoUsuario::collection($usuarios);
    }

    public function store(SolicitudUsuario $solicitud): JsonResponse
    {
        $datos = $solicitud->validated();
        $datos['password'] = Hash::make(Str::random(32));

        $usuario = Usuario::create($datos);

        Password::sendResetLink(['email' => $usuario->email]);

        return response()->json(['data' => new RecursoUsuario($usuario)], 201);
    }

    public function update(SolicitudUsuario $solicitud, Usuario $usuario): JsonResponse
    {
        $usuario->update($solicitud->validated());

        return response()->json(['data' => new RecursoUsuario($usuario)]);
    }

    public function destroy(Usuario $usuario): JsonResponse
    {
        $usuario->delete();

        return response()->json(['mensaje' => 'Usuario eliminado.']);
    }

    public function restablecerContrasena(Usuario $usuario): JsonResponse
    {
        /** @var Usuario $superAdmin */
        $superAdmin = Auth::guard('web')->user();

        app(RestablecerContrasenaUsuario::class)->ejecutar($usuario, $superAdmin);

        return response()->json(['mensaje' => 'Correo de restablecimiento enviado.']);
    }
}
