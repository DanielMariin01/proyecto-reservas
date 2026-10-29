<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lista fija de íconos para los campos "ícono" de la plantilla de
 * landing (portada, texto_con_imagen) — nunca texto libre, para que el
 * Select del Builder y el mapa a lucide-react en React (iconoBloque.tsx)
 * se mantengan sincronizados.
 */
enum IconoBloque: string
{
    case Escudo = 'escudo';
    case Reloj = 'reloj';
    case Estrella = 'estrella';
    case Premio = 'premio';
    case Corazon = 'corazon';
    case Usuarios = 'usuarios';
    case Destellos = 'destellos';
    case Ubicacion = 'ubicacion';
    case Billete = 'billete';
    case PulgarArriba = 'pulgar_arriba';
    case Telefono = 'telefono';
    case Tijeras = 'tijeras';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Escudo => 'Escudo (garantía)',
            self::Reloj => 'Reloj (horario/puntualidad)',
            self::Estrella => 'Estrella (calidad)',
            self::Premio => 'Premio (experiencia)',
            self::Corazon => 'Corazón (atención)',
            self::Usuarios => 'Usuarios (equipo)',
            self::Destellos => 'Destellos (resultados)',
            self::Ubicacion => 'Ubicación',
            self::Billete => 'Billete (precio)',
            self::PulgarArriba => 'Pulgar arriba (satisfacción)',
            self::Telefono => 'Teléfono (contacto)',
            self::Tijeras => 'Tijeras (servicio)',
        };
    }
}
