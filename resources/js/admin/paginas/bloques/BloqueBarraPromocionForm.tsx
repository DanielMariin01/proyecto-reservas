import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import CamposColorLibre from './CamposColorLibre';
import { leerTexto } from './leer';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

export default function BloqueBarraPromocionForm({ datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const texto = leerTexto(datos, 'texto');
    const textoBoton = leerTexto(datos, 'textoBoton');
    const enlaceBoton = leerTexto(datos, 'enlaceBoton');
    const colorFondo = leerTexto(datos, 'colorFondo', '#673DE6');
    const colorTexto = leerTexto(datos, 'colorTexto', '#FFFFFF');

    return (
        <div className="space-y-4">
            <CampoTexto etiqueta="Texto" value={texto} onChange={(evento) => onChange({ ...datos, texto: evento.target.value })} />
            <CampoTexto
                etiqueta="Texto del botón (opcional)"
                value={textoBoton}
                onChange={(evento) => onChange({ ...datos, textoBoton: evento.target.value })}
            />
            <CampoTexto
                etiqueta="Enlace del botón"
                value={enlaceBoton}
                onChange={(evento) => onChange({ ...datos, enlaceBoton: evento.target.value })}
            />
            <CamposColorLibre
                colorFondo={colorFondo}
                colorTexto={colorTexto}
                onCambiarColorFondo={(valor) => onChange({ ...datos, colorFondo: valor })}
                onCambiarColorTexto={(valor) => onChange({ ...datos, colorTexto: valor })}
            />
        </div>
    );
}
