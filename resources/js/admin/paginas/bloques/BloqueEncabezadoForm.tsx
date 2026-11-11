import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import CamposColorLibre from './CamposColorLibre';
import { leerArray, leerTexto } from './leer';
import Repetidor from './Repetidor';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

interface Enlace {
    texto: string;
    url: string;
}

const MAXIMO_ENLACES = 5;

export default function BloqueEncabezadoForm({ datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const enlaces = leerArray<Enlace>(datos, 'enlaces');
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', '#3E4043');

    return (
        <div className="space-y-4">
            <Repetidor
                etiqueta="Enlaces de navegación"
                items={enlaces}
                maximo={MAXIMO_ENLACES}
                itemPorDefecto={{ texto: '', url: '' }}
                textoAgregar="Agregar enlace"
                onChange={(items) => onChange({ ...datos, enlaces: items })}
                renderItem={(enlace, onCambiar) => (
                    <div className="grid gap-3 sm:grid-cols-2">
                        <CampoTexto etiqueta="Texto" value={enlace.texto} onChange={(evento) => onCambiar({ ...enlace, texto: evento.target.value })} />
                        <CampoTexto etiqueta="URL" value={enlace.url} onChange={(evento) => onCambiar({ ...enlace, url: evento.target.value })} />
                    </div>
                )}
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
