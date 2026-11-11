import type { ReactElement } from 'react';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import CampoTexto from '@/componentes/CampoTexto';
import CampoImagenBloque from './CampoImagenBloque';
import { leerArray, leerBool, leerImagen, leerTexto } from './leer';
import { OPCIONES_ICONO } from './opcionesIcono';
import Repetidor from './Repetidor';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

interface IconoConEtiqueta {
    icono: string;
    etiqueta: string;
}

const MAXIMO_ICONOS = 4;

export default function BloquePortadaForm({ slug, datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const titulo = leerTexto(datos, 'titulo');
    const overlay = leerTexto(datos, 'overlay', 'oscura');
    const horarioHoy = leerTexto(datos, 'horarioHoy');
    const descripcion = leerTexto(datos, 'descripcion');
    const textoBotonPrincipal = leerTexto(datos, 'textoBotonPrincipal', 'Reservar cita');
    const mostrarBotonVideo = leerBool(datos, 'mostrarBotonVideo');
    const enlaceVideo = leerTexto(datos, 'enlaceVideo');
    const iconos = leerArray<IconoConEtiqueta>(datos, 'iconos');
    const imagen = leerImagen(datos, 'imagen');
    const textoManuscrito = leerTexto(datos, 'textoManuscrito');

    return (
        <div className="space-y-4">
            <CampoTexto etiqueta="Título" value={titulo} onChange={(evento) => onChange({ ...datos, titulo: evento.target.value })} />

            <div className="flex flex-col gap-1">
                <label htmlFor="portada-overlay" className="text-sm font-medium text-tinta">
                    Capa sobre la foto
                </label>
                <select
                    id="portada-overlay"
                    value={overlay}
                    onChange={(evento) => onChange({ ...datos, overlay: evento.target.value })}
                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                >
                    <option value="oscura">Oscura (foto clara, texto blanco)</option>
                    <option value="clara">Clara (foto oscura, texto oscuro)</option>
                </select>
            </div>

            <CampoTexto
                etiqueta="Horario de hoy"
                value={horarioHoy}
                onChange={(evento) => onChange({ ...datos, horarioHoy: evento.target.value })}
                placeholder='Ej. "Hoy: 9:00 a. m. – 7:00 p. m."'
            />

            <div className="flex flex-col gap-1">
                <label htmlFor="portada-descripcion" className="text-sm font-medium text-tinta">
                    Descripción
                </label>
                <textarea
                    id="portada-descripcion"
                    value={descripcion}
                    onChange={(evento) => onChange({ ...datos, descripcion: evento.target.value })}
                    rows={3}
                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                />
            </div>

            <CampoTexto
                etiqueta="Texto del botón principal"
                value={textoBotonPrincipal}
                onChange={(evento) => onChange({ ...datos, textoBotonPrincipal: evento.target.value })}
            />

            <CampoInterruptor
                etiqueta='Mostrar botón "Ver video"'
                value={mostrarBotonVideo}
                onChange={(valor) => onChange({ ...datos, mostrarBotonVideo: valor })}
            />

            {mostrarBotonVideo && (
                <CampoTexto
                    etiqueta="Enlace del video"
                    value={enlaceVideo}
                    onChange={(evento) => onChange({ ...datos, enlaceVideo: evento.target.value })}
                />
            )}

            <Repetidor
                etiqueta="Fila de íconos"
                items={iconos}
                maximo={MAXIMO_ICONOS}
                itemPorDefecto={{ icono: OPCIONES_ICONO[0].valor, etiqueta: '' }}
                textoAgregar="Agregar ícono"
                onChange={(items) => onChange({ ...datos, iconos: items })}
                renderItem={(icono, onCambiar) => (
                    <div className="grid gap-3 sm:grid-cols-2">
                        <select
                            value={icono.icono}
                            onChange={(evento) => onCambiar({ ...icono, icono: evento.target.value })}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            {OPCIONES_ICONO.map((opcion) => (
                                <option key={opcion.valor} value={opcion.valor}>
                                    {opcion.etiqueta}
                                </option>
                            ))}
                        </select>
                        <CampoTexto
                            etiqueta="Etiqueta corta"
                            value={icono.etiqueta}
                            onChange={(evento) => onCambiar({ ...icono, etiqueta: evento.target.value })}
                        />
                    </div>
                )}
            />

            <CampoImagenBloque slug={slug} etiqueta="Foto de fondo" value={imagen} onChange={(ruta) => onChange({ ...datos, imagen: ruta })} />

            <CampoTexto
                etiqueta="Texto manuscrito (opcional)"
                value={textoManuscrito}
                onChange={(evento) => onChange({ ...datos, textoManuscrito: evento.target.value })}
            />
        </div>
    );
}
