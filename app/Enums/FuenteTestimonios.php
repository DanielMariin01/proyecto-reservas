<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * El bloque `testimonios` se guarda pensando en dos orígenes posibles:
 * contenido manual elegido a mano por el super admin, o las reseñas
 * reales del sistema (App\Models\Resena). La forma de cada testimonio
 * (cita, nombre, foto, estrellas) es la misma sin importar su origen,
 * así que RecursoPaginaPublica resuelve cada caso sin tocar el Builder
 * ni React.
 */
enum FuenteTestimonios: string
{
    case Manual = 'manual';
    case Sistema = 'sistema';
}
