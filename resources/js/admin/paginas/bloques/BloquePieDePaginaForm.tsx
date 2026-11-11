import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import { leerTexto } from './leer';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

export default function BloquePieDePaginaForm({ datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const facebookUrl = leerTexto(datos, 'facebookUrl');
    const instagramUrl = leerTexto(datos, 'instagramUrl');

    return (
        <div className="space-y-4">
            <CampoTexto
                etiqueta="Enlace de Facebook"
                value={facebookUrl}
                onChange={(evento) => onChange({ ...datos, facebookUrl: evento.target.value })}
            />
            <CampoTexto
                etiqueta="Enlace de Instagram"
                value={instagramUrl}
                onChange={(evento) => onChange({ ...datos, instagramUrl: evento.target.value })}
            />
        </div>
    );
}
