<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * RF-BLQ-03/14.3: los tipos de bloque que el super admin puede agregar al
 * armar la página (skill diseno-reservas, sección 6). Cursos y productos
 * ya están aquí porque RF-BLQ-04 pide que el registro de tipos no cambie
 * cuando se agregue uno nuevo, pero su contenido real (RF-CUR, tienda)
 * es de Fase 2/3: mientras tanto el bloque se guarda y se puede ordenar,
 * pero React lo muestra como "Próximamente".
 */
enum TipoBloque: string
{
    case Portada = 'portada';
    case Equipo = 'equipo';
    case Servicios = 'servicios';
    case Cursos = 'cursos';
    case Productos = 'productos';
    case Texto = 'texto';
    case TextoConImagen = 'texto_con_imagen';
    case Galeria = 'galeria';
    case Ubicacion = 'ubicacion';
    case Whatsapp = 'whatsapp';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Portada => 'Portada',
            self::Equipo => 'Nuestro equipo',
            self::Servicios => 'Servicios',
            self::Cursos => 'Cursos',
            self::Productos => 'Productos',
            self::Texto => 'Texto',
            self::TextoConImagen => 'Texto con imagen',
            self::Galeria => 'Galería',
            self::Ubicacion => 'Ubicación',
            self::Whatsapp => 'WhatsApp flotante',
        };
    }

    /**
     * Fase 2/3: el bloque existe en el registro pero no tiene datos reales
     * detrás todavía (no hay modelos Curso/Producto).
     */
    public function disponible(): bool
    {
        return $this !== self::Cursos && $this !== self::Productos;
    }
}
