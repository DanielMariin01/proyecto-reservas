import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import CamposColorLibre from './CamposColorLibre';
import { leerTexto } from './leer';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

export default function BloqueTextoForm({ datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const titulo = leerTexto(datos, 'titulo');
    const parrafo = leerTexto(datos, 'parrafo');
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', '#3E4043');

    return (
        <div className="space-y-4">
            <CampoTexto etiqueta="Título" value={titulo} onChange={(evento) => onChange({ ...datos, titulo: evento.target.value })} />

            <div className="flex flex-col gap-1">
                <label htmlFor="texto-parrafo" className="text-sm font-medium text-tinta">
                    Párrafo
                </label>
                <textarea
                    id="texto-parrafo"
                    value={parrafo}
                    onChange={(evento) => onChange({ ...datos, parrafo: evento.target.value })}
                    rows={4}
                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                />
                <p className="text-sm text-texto-suave">Máximo 70 caracteres por línea para que se lea bien.</p>
            </div>

            <CamposColorLibre
                colorFondo={colorFondo}
                colorTexto={colorTexto}
                onCambiarColorFondo={(valor) => onChange({ ...datos, colorFondo: valor })}
                onCambiarColorTexto={(valor) => onChange({ ...datos, colorTexto: valor })}
            />
        </div>
    );
}
