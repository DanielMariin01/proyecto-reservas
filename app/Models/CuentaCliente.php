<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CuentaClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Cuenta global del cliente final (T11): a diferencia de Usuario, no
 * pertenece a un negocio — un mismo cliente reserva en varios negocios con
 * la misma cuenta. Solo se entra con Google (ServicioCuentaCliente), por
 * eso no hay columna de contraseña. Nunca se usa en los paneles admin/app:
 * su guard ('cliente') es aparte.
 */
final class CuentaCliente extends Authenticatable
{
    /** @use HasFactory<CuentaClienteFactory> */
    use HasFactory;

    protected $table = 'cuentas_cliente';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'correo',
        'google_id',
    ];

    /**
     * @return HasMany<Cliente, $this>
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }
}
