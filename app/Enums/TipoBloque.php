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
    case BarraPromocion = 'barra_promocion';
    case Encabezado = 'encabezado';
    case Portada = 'portada';
    case Equipo = 'equipo';
    case Servicios = 'servicios';
    case Cursos = 'cursos';
    case Productos = 'productos';
    case Texto = 'texto';
    case TextoConImagen = 'texto_con_imagen';
    case Galeria = 'galeria';
    case BannerOferta = 'banner_oferta';
    case Testimonios = 'testimonios';
    case Ubicacion = 'ubicacion';
    case Whatsapp = 'whatsapp';
    case PieDePagina = 'pie_de_pagina';

    public function etiqueta(): string
    {
        return match ($this) {
            self::BarraPromocion => 'Barra de promoción',
            self::Encabezado => 'Encabezado',
            self::Portada => 'Portada',
            self::Equipo => 'Nuestro equipo',
            self::Servicios => 'Servicios',
            self::Cursos => 'Cursos',
            self::Productos => 'Productos',
            self::Texto => 'Texto',
            self::TextoConImagen => 'Texto con imagen',
            self::Galeria => 'Galería',
            self::BannerOferta => 'Banner de oferta',
            self::Testimonios => 'Testimonios',
            self::Ubicacion => 'Ubicación',
            self::Whatsapp => 'WhatsApp flotante',
            self::PieDePagina => 'Pie de página',
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
