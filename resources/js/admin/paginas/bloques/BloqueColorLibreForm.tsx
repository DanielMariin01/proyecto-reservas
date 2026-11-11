import type { ReactElement } from 'react';
import CamposColorLibre from './CamposColorLibre';
import { leerTexto } from './leer';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

interface Propiedades extends PropiedadesFormularioBloque {
    colorFondoPorDefecto?: string;
    colorTextoPorDefecto?: string;
}

/**
 * Equipo, servicios, cursos, productos, ubicación y whatsapp no tienen
 * campos propios (RF-BLQ-02: no guardan datos, los consultan al sistema
 * al mostrarse) — solo el color libre, igual que en el Builder de
 * Filament.
 */
export default function BloqueColorLibreForm({
    datos,
    onChange,
    colorFondoPorDefecto = '#FFFFFF',
    colorTextoPorDefecto = '#3E4043',
}: Propiedades): ReactElement {
    const colorFondo = leerTexto(datos, 'colorFondo', colorFondoPorDefecto);
    const colorTexto = leerTexto(datos, 'colorTexto', colorTextoPorDefecto);

    return (
        <CamposColorLibre
            colorFondo={colorFondo}
            colorTexto={colorTexto}
            onCambiarColorFondo={(valor) => onChange({ ...datos, colorFondo: valor })}
            onCambiarColorTexto={(valor) => onChange({ ...datos, colorTexto: valor })}
        />
    );
}
