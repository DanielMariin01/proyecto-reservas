import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import CampoImagenBloque from './CampoImagenBloque';
import CamposColorLibre from './CamposColorLibre';
import { leerImagen, leerTexto } from './leer';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

const OPCIONES_TEMA: Array<{ valor: string; etiqueta: string }> = [
    { valor: 'ninguno', etiqueta: 'Ninguno' },
    { valor: 'halloween', etiqueta: 'Halloween' },
    { valor: 'navidad', etiqueta: 'Navidad' },
    { valor: 'amor_amistad', etiqueta: 'Amor y amistad' },
    { valor: 'anio_nuevo', etiqueta: 'Año nuevo' },
    { valor: 'dia_madre', etiqueta: 'Día de la madre' },
];

export default function BloqueBannerOfertaForm({ slug, datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const etiqueta = leerTexto(datos, 'etiqueta');
    const titulo = leerTexto(datos, 'titulo');
    const descripcion = leerTexto(datos, 'descripcion');
    const textoBoton = leerTexto(datos, 'textoBoton', 'Reserva tu cupo');
    const enlaceBoton = leerTexto(datos, 'enlaceBoton');
    const imagen = leerImagen(datos, 'imagen');
    const textoManuscrito = leerTexto(datos, 'textoManuscrito');
    const temaDecorativo = leerTexto(datos, 'temaDecorativo', 'ninguno');
    const colorFondo = leerTexto(datos, 'colorFondo', '#673DE6');
    const colorTexto = leerTexto(datos, 'colorTexto', '#FFFFFF');

    return (
        <div className="space-y-4">
            <CampoTexto etiqueta="Etiqueta" value={etiqueta} onChange={(evento) => onChange({ ...datos, etiqueta: evento.target.value })} />
            <CampoTexto etiqueta="Título" value={titulo} onChange={(evento) => onChange({ ...datos, titulo: evento.target.value })} />

            <div className="flex flex-col gap-1">
                <label htmlFor="banner-descripcion" className="text-sm font-medium text-tinta">
                    Descripción
                </label>
                <textarea
                    id="banner-descripcion"
                    value={descripcion}
                    onChange={(evento) => onChange({ ...datos, descripcion: evento.target.value })}
                    rows={3}
                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                />
            </div>

            <CampoTexto etiqueta="Texto del botón" value={textoBoton} onChange={(evento) => onChange({ ...datos, textoBoton: evento.target.value })} />
            <CampoTexto
                etiqueta="Enlace del botón"
                value={enlaceBoton}
                onChange={(evento) => onChange({ ...datos, enlaceBoton: evento.target.value })}
            />

            <CampoImagenBloque slug={slug} etiqueta="Imagen" value={imagen} onChange={(ruta) => onChange({ ...datos, imagen: ruta })} />

            <CampoTexto
                etiqueta="Texto manuscrito (opcional)"
                value={textoManuscrito}
                onChange={(evento) => onChange({ ...datos, textoManuscrito: evento.target.value })}
            />

            <div className="flex flex-col gap-1">
                <label htmlFor="banner-tema" className="text-sm font-medium text-tinta">
                    Fondo decorativo temático
                </label>
                <select
                    id="banner-tema"
                    value={temaDecorativo}
                    onChange={(evento) => onChange({ ...datos, temaDecorativo: evento.target.value })}
                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                >
                    {OPCIONES_TEMA.map((opcion) => (
                        <option key={opcion.valor} value={opcion.valor}>
                            {opcion.etiqueta}
                        </option>
                    ))}
                </select>
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
