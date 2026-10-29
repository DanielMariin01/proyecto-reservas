<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * El bloque `testimonios` se guarda ya pensando en conectarse más
 * adelante al sistema real de reseñas (tarea aparte, todavía no
 * construida): la forma de cada testimonio (cita, nombre, foto,
 * estrellas) es la misma sin importar su origen, así que cuando ese
 * sistema exista, agregar `Sistema` aquí y resolverlo en
 * RecursoPaginaPublica no debería tocar ni el Builder ni React.
 *
 * Por ahora solo `Manual` está implementado; el campo existe para no
 * tener que rehacer la forma del bloque cuando llegue el otro.
 */
enum FuenteTestimonios: string
{
    case Manual = 'manual';
}
