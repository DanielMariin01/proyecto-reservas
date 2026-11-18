import type { ReactElement } from 'react';
import CampoColor from '@/componentes/CampoColor';
import CamposColorLibre from './CamposColorLibre';
import { advertenciaContraste } from './contraste';
import { leerTexto } from './leer';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

const COLOR_TEXTO_POR_DEFECTO = '#3E4043';
const COLOR_FONDO_CATEGORIAS_POR_DEFECTO = '#E1EEFF';
const COLOR_TEXTO_CATEGORIAS_POR_DEFECTO = '#673DE6';

/**
 * El bloque `servicios` no guarda servicios (RF-BLQ-02), solo color libre
 * — pero a diferencia de equipo/cursos/productos/ubicación/whatsapp
 * (`BloqueColorLibreForm` genérico), este bloque suma 2 personalizaciones
 * propias que no aplican a los demás: el color del título de la sección
 * y el color de la categoría activa en la barra de filtro.
 */
export default function BloqueServiciosForm({ datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', COLOR_TEXTO_POR_DEFECTO);
    const colorTitulo = leerTexto(datos, 'colorTitulo', colorTexto);
    const colorFondoCategorias = leerTexto(datos, 'colorFondoCategorias', COLOR_FONDO_CATEGORIAS_POR_DEFECTO);
    const colorTextoCategorias = leerTexto(datos, 'colorTextoCategorias', COLOR_TEXTO_CATEGORIAS_POR_DEFECTO);

    return (
        <div className="space-y-4">
            <CamposColorLibre
                colorFondo={colorFondo}
                colorTexto={colorTexto}
                onCambiarColorFondo={(valor) => onChange({ ...datos, colorFondo: valor })}
                onCambiarColorTexto={(valor) => onChange({ ...datos, colorTexto: valor })}
            />

            <CampoColor
                etiqueta="Color del título de la sección"
                value={colorTitulo}
                onChange={(valor) => onChange({ ...datos, colorTitulo: valor })}
                ayuda={advertenciaContraste(colorFondo, colorTitulo) ?? undefined}
            />

            <div className="grid gap-4 sm:grid-cols-2">
                <CampoColor
                    etiqueta="Color de fondo (categoría activa)"
                    value={colorFondoCategorias}
                    onChange={(valor) => onChange({ ...datos, colorFondoCategorias: valor })}
                />
                <CampoColor
                    etiqueta="Color de texto (categoría activa)"
                    value={colorTextoCategorias}
                    onChange={(valor) => onChange({ ...datos, colorTextoCategorias: valor })}
                    ayuda={advertenciaContraste(colorFondoCategorias, colorTextoCategorias) ?? undefined}
                />
            </div>
        </div>
    );
}
