<?php

declare(strict_types=1);

namespace App\Enums;

enum RolNegocio: string
{
    case Dueno = 'dueno';
    case Recepcion = 'recepcion';
    case Profesional = 'profesional';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Dueno => 'Dueño',
            self::Recepcion => 'Recepción',
            self::Profesional => 'Profesional',
        };
    }

    /**
     * @return array<int, Permiso>
     */
    public function permisos(): array
    {
        return match ($this) {
            self::Dueno => [
                Permiso::GestionarNegocio,
                Permiso::GestionarUsuarios,
                Permiso::GestionarCatalogo,
                Permiso::GestionarCitasTodas,
                Permiso::GestionarPagos,
                Permiso::GestionarClientes,
                Permiso::VerReportes,
                Permiso::GestionarCursosProductos,
            ],
            self::Recepcion => [
                Permiso::GestionarCitasTodas,
                Permiso::GestionarPagos,
                Permiso::GestionarClientes,
            ],
            self::Profesional => [
                Permiso::GestionarCitasPropias,
            ],
        };
    }
}
