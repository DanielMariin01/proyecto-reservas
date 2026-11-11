<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\InvitarUsuarioANegocio;
use App\DatosTransferencia\DatosInvitacionUsuario;
use App\Enums\RolNegocio;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudActualizarUsuarioNegocio;
use App\Http\Requests\App\SolicitudInvitarUsuario;
use App\Http\Resources\App\RecursoUsuarioNegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

final class UsuarioController extends Controller
{
    /**
     * El parametro Negocio $negocio no se usa directamente en destroy(),
     * pero es necesario: Laravel solo resuelve el binding implicito de
     * {negocio:slug} cuando el metodo del controlador lo tipa, y
     * ResolverNegocioDesdeRuta depende de que ese binding ya este
     * resuelto para fijar el contexto del negocio actual.
     */
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Usuario::class);

        $usuarios = Usuario::query()->deNegocio($negocio->id)->with('roles')->orderBy('nombre')->get();

        return RecursoUsuarioNegocio::collection($usuarios);
    }

    public function store(SolicitudInvitarUsuario $solicitud, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Usuario::class);

        $datos = $solicitud->validated();

        $usuario = app(InvitarUsuarioANegocio::class)->ejecutar(new DatosInvitacionUsuario(
            negocio: $negocio,
            nombre: $datos['nombre'],
            email: $datos['email'],
            rol: RolNegocio::from($datos['rol']),
            invitadoPor: $this->usuarioActual(),
        ));

        return response()->json(['data' => new RecursoUsuarioNegocio($usuario->load('roles'))], 201);
    }

    public function update(SolicitudActualizarUsuarioNegocio $solicitud, Negocio $negocio, int $usuario): JsonResponse
    {
        $usuarioModelo = Usuario::query()->findOrFail($usuario);
        $this->authorize('update', $usuarioModelo);

        $datos = $solicitud->validated();
        $rol = $datos['rol'];
        unset($datos['rol']);

        $usuarioModelo->update($datos);

        app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);
        $usuarioModelo->syncRoles([$rol]);

        return response()->json(['data' => new RecursoUsuarioNegocio($usuarioModelo->load('roles'))]);
    }

    public function destroy(Negocio $negocio, int $usuario): JsonResponse
    {
        $usuarioModelo = Usuario::query()->findOrFail($usuario);
        $this->authorize('delete', $usuarioModelo);

        $usuarioModelo->delete();

        return response()->json(['mensaje' => 'Usuario eliminado.']);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
