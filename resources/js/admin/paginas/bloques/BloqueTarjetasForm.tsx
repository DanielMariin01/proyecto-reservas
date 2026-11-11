import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import CampoImagenBloque from './CampoImagenBloque';
import CamposColorLibre from './CamposColorLibre';
import { leerArray, leerNumero, leerTexto } from './leer';
import Repetidor from './Repetidor';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

interface TarjetaDatos {
    imagen: string | null;
    titulo: string;
    descripcion: string;
}

const MAXIMO_TARJETAS = 9;

export default function BloqueTarjetasForm({ slug, datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const columnas = leerNumero(datos, 'columnas', 2);
    const tarjetas = leerArray<TarjetaDatos>(datos, 'tarjetas');
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', '#3E4043');

    return (
        <div className="space-y-4">
            <div className="flex flex-col gap-1">
                <label htmlFor="tarjetas-columnas" className="text-sm font-medium text-tinta">
                    Columnas en escritorio
                </label>
                <select
                    id="tarjetas-columnas"
                    value={columnas}
                    onChange={(evento) => onChange({ ...datos, columnas: Number(evento.target.value) })}
                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                >
                    <option value={1}>1 columna</option>
                    <option value={2}>2 columnas</option>
                    <option value={3}>3 columnas</option>
                </select>
            </div>

            <Repetidor
                etiqueta="Tarjetas"
                items={tarjetas}
                maximo={MAXIMO_TARJETAS}
                itemPorDefecto={{ imagen: null, titulo: '', descripcion: '' }}
                textoAgregar="Agregar tarjeta"
                onChange={(items) => onChange({ ...datos, tarjetas: items })}
                renderItem={(tarjeta, onCambiar) => (
                    <div className="space-y-3">
                        <CampoImagenBloque slug={slug} etiqueta="Imagen" value={tarjeta.imagen} onChange={(ruta) => onCambiar({ ...tarjeta, imagen: ruta })} />
                        <CampoTexto etiqueta="Título" value={tarjeta.titulo} onChange={(evento) => onCambiar({ ...tarjeta, titulo: evento.target.value })} />
                        <CampoTexto
                            etiqueta="Descripción"
                            value={tarjeta.descripcion}
                            onChange={(evento) => onCambiar({ ...tarjeta, descripcion: evento.target.value })}
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
