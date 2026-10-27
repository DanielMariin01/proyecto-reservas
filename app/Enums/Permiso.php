<?php

declare(strict_types=1);

namespace App\Enums;

enum Permiso: string
{
    case GestionarNegocio = 'gestionar_negocio';
    case GestionarUsuarios = 'gestionar_usuarios';
    case GestionarCatalogo = 'gestionar_catalogo';
    case GestionarCitasPropias = 'gestionar_citas_propias';
    case GestionarCitasTodas = 'gestionar_citas_todas';
    case GestionarPagos = 'gestionar_pagos';
    case GestionarClientes = 'gestionar_clientes';
    case VerReportes = 'ver_reportes';
    case GestionarCursosProductos = 'gestionar_cursos_productos';
}
