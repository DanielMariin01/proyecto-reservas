import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import CampoImagenBloque from './CampoImagenBloque';
import CamposColorLibre from './CamposColorLibre';
import { leerArray, leerTexto } from './leer';
import Repetidor from './Repetidor';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

interface DisenoDatos {
    id?: string;
    imagen: string | null;
    titulo: string;
    descripcion: string;
}

const MAXIMO_DISENOS = 12;

/**
 * RF-HCL-08-b: cada diseño necesita un `id` estable para que un cliente lo
 * pueda marcar como favorito desde /mi-cuenta. El Repetidor reutiliza el
 * mismo `itemPorDefecto` por referencia en cada "Agregar", así que el id se
 * genera aquí, al guardar, no en el objeto por defecto.
 */
function conIdEstable(items: DisenoDatos[]): DisenoDatos[] {
    return items.map((item) => (item.id ? item : { ...item, id: crypto.randomUUID() }));
}

export default function BloqueMisDisenosForm({ slug, datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const items = leerArray<DisenoDatos>(datos, 'items');
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', '#3E4043');

    return (
        <div className="space-y-4">
            <Repetidor
                etiqueta="Diseños"
                items={items}
                maximo={MAXIMO_DISENOS}
                itemPorDefecto={{ imagen: null, titulo: '', descripcion: '' }}
                textoAgregar="Agregar diseño"
                onChange={(nuevos) => onChange({ ...datos, items: conIdEstable(nuevos) })}
                renderItem={(item, onCambiar) => (
                    <div className="space-y-3">
                        <CampoImagenBloque slug={slug} etiqueta="Imagen" value={item.imagen} onChange={(ruta) => onCambiar({ ...item, imagen: ruta })} />
                        <CampoTexto etiqueta="Título" value={item.titulo} onChange={(evento) => onCambiar({ ...item, titulo: evento.target.value })} />
                        <CampoTexto
                            etiqueta="Descripción"
                            value={item.descripcion}
                            onChange={(evento) => onCambiar({ ...item, descripcion: evento.target.value })}
                        />
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
