import type { ReactElement } from 'react';
import CampoImagenesBloque from './CampoImagenesBloque';
import CamposColorLibre from './CamposColorLibre';
import { leerArray, leerTexto } from './leer';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

export default function BloqueGaleriaForm({ slug, datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const fotos = leerArray<string>(datos, 'fotos');
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', '#3E4043');

    return (
        <div className="space-y-4">
            <CampoImagenesBloque slug={slug} etiqueta="Fotos" value={fotos} onChange={(rutas) => onChange({ ...datos, fotos: rutas })} />

            <CamposColorLibre
                colorFondo={colorFondo}
                colorTexto={colorTexto}
                onCambiarColorFondo={(valor) => onChange({ ...datos, colorFondo: valor })}
                onCambiarColorTexto={(valor) => onChange({ ...datos, colorTexto: valor })}
            />
        </div>
    );
}
