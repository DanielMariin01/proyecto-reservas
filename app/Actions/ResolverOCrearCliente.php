<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Cliente;
use App\Models\CuentaCliente;
use App\Models\Negocio;

/**
 * Compartido por CrearReserva y CrearPedido: buscar o crear el Cliente a
 * partir de los datos que deja un visitante anónimo en la página pública
 * (sección 15.1), incluido el enlace con una CuentaCliente existente por
 * correo (T11).
 */
final class ResolverOCrearCliente
{
    public function ejecutar(Negocio $negocio, string $nombre, string $celular, ?string $correo): Cliente
    {
        $clienteExistente = Cliente::query()
            ->where('negocio_id', $negocio->id)
            ->where('celular', $celular)
            ->first();

        if ($clienteExistente !== null) {
            return $clienteExistente;
        }

        return Cliente::create([
            'negocio_id' => $negocio->id,
            'cuenta_cliente_id' => $this->cuentaClienteParaCorreo($correo),
            'nombre' => $nombre,
            'celular' => $celular,
            'correo' => $correo,
        ]);
    }

    /**
     * T11: si el correo de una reserva/pedido nuevo (sin cuenta) ya tiene
     * una CuentaCliente, el Cliente que se crea ahora queda enlazado de
     * una vez, sin esperar a que esa persona vuelva a entrar con Google.
     */
    private function cuentaClienteParaCorreo(?string $correo): ?int
    {
        if ($correo === null) {
            return null;
        }

        return CuentaCliente::query()->where('correo', $correo)->value('id');
    }
}
