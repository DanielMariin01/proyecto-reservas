import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import CampoImagenBloque from './CampoImagenBloque';
import CamposColorLibre from './CamposColorLibre';
import { leerArray, leerImagen, leerTexto } from './leer';
import { OPCIONES_ICONO } from './opcionesIcono';
import Repetidor from './Repetidor';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

interface Motivo {
    icono: string;
    titulo: string;
    descripcion: string;
}

const MAXIMO_MOTIVOS = 4;

export default function BloqueTextoConImagenForm({ slug, datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const etiqueta = leerTexto(datos, 'etiqueta');
    const titulo = leerTexto(datos, 'titulo');
    const parrafo = leerTexto(datos, 'parrafo');
    const fraseResaltada = leerTexto(datos, 'fraseResaltada');
    const motivos = leerArray<Motivo>(datos, 'motivos');
    const textoBoton = leerTexto(datos, 'textoBoton');
    const enlaceBoton = leerTexto(datos, 'enlaceBoton');
    const imagen = leerImagen(datos, 'imagen');
    const textoManuscrito = leerTexto(datos, 'textoManuscrito');
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', '#3E4043');

    return (
        <div className="space-y-4">
            <CampoTexto etiqueta="Etiqueta" value={etiqueta} onChange={(evento) => onChange({ ...datos, etiqueta: evento.target.value })} />
            <CampoTexto etiqueta="Título" value={titulo} onChange={(evento) => onChange({ ...datos, titulo: evento.target.value })} />

            <div className="flex flex-col gap-1">
                <label htmlFor="texto-con-imagen-parrafo" className="text-sm font-medium text-tinta">
                    Párrafo
                </label>
                <textarea
                    id="texto-con-imagen-parrafo"
                    value={parrafo}
                    onChange={(evento) => onChange({ ...datos, parrafo: evento.target.value })}
                    rows={4}
                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                />
                <p className="text-sm text-texto-suave">Modo simple: se ignora si agregas al menos un motivo abajo.</p>
            </div>

            <div className="space-y-1">
                <CampoTexto
                    etiqueta="Frase resaltada"
                    value={fraseResaltada}
                    onChange={(evento) => onChange({ ...datos, fraseResaltada: evento.target.value })}
                />
                <p className="text-sm text-texto-suave">Debe aparecer tal cual dentro del párrafo; si no coincide, no se resalta nada.</p>
            </div>

            <Repetidor
                etiqueta='Motivos ("por qué elegirnos")'
                items={motivos}
                maximo={MAXIMO_MOTIVOS}
                itemPorDefecto={{ icono: OPCIONES_ICONO[0].valor, titulo: '', descripcion: '' }}
                textoAgregar="Agregar motivo"
                onChange={(items) => onChange({ ...datos, motivos: items })}
                renderItem={(motivo, onCambiar) => (
                    <div className="grid gap-3 sm:grid-cols-3">
                        <select
                            value={motivo.icono}
                            onChange={(evento) => onCambiar({ ...motivo, icono: evento.target.value })}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            {OPCIONES_ICONO.map((opcion) => (
                                <option key={opcion.valor} value={opcion.valor}>
                                    {opcion.etiqueta}
                                </option>
                            ))}
                        </select>
                        <CampoTexto
                            etiqueta="Título corto"
                            value={motivo.titulo}
                            onChange={(evento) => onCambiar({ ...motivo, titulo: evento.target.value })}
                        />
                        <CampoTexto
                            etiqueta="Descripción"
                            value={motivo.descripcion}
                            onChange={(evento) => onCambiar({ ...motivo, descripcion: evento.target.value })}
                        />
                    </div>
                )}
            />

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

            <CamposColorLibre
                colorFondo={colorFondo}
                colorTexto={colorTexto}
                onCambiarColorFondo={(valor) => onChange({ ...datos, colorFondo: valor })}
                onCambiarColorTexto={(valor) => onChange({ ...datos, colorTexto: valor })}
            />
        </div>
    );
}
