import type { ReactElement } from 'react';
import { useState } from 'react';
import { ChevronDown, ChevronUp, Plus, Trash2 } from 'lucide-react';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import type { Bloque } from '../../api/paginaWeb';
import FormularioBloque from './FormularioBloque';
import { metaDelTipo, TIPOS_BLOQUE } from './tiposBloque';

interface Propiedades {
    slug: string;
    bloques: Bloque[];
    onChange: (bloques: Bloque[]) => void;
}

export default function BuilderBloques({ slug, bloques, onChange }: Propiedades): ReactElement {
    const [expandidos, setExpandidos] = useState<Set<number>>(new Set());
    const [tipoAAgregar, setTipoAAgregar] = useState(TIPOS_BLOQUE[0].tipo);

    function alternarExpandido(indice: number): void {
        setExpandidos((actual) => {
            const siguiente = new Set(actual);

            if (siguiente.has(indice)) {
                siguiente.delete(indice);
            } else {
                siguiente.add(indice);
            }

            return siguiente;
        });
    }

    function agregarBloque(): void {
        onChange([...bloques, { type: tipoAAgregar, data: {} }]);
    }

    function quitarBloque(indice: number): void {
        onChange(bloques.filter((_bloque, posicion) => posicion !== indice));
    }

    function moverBloque(indice: number, direccion: -1 | 1): void {
        const destino = indice + direccion;

        if (destino < 0 || destino >= bloques.length) {
            return;
        }

        const copia = [...bloques];
        [copia[indice], copia[destino]] = [copia[destino], copia[indice]];
        onChange(copia);
    }

    function cambiarDatosBloque(indice: number, datos: Record<string, unknown>): void {
        onChange(bloques.map((bloque, posicion) => (posicion === indice ? { ...bloque, data: datos } : bloque)));
    }

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-end gap-3">
                <div className="flex flex-col gap-1">
                    <label htmlFor="tipo-bloque-nuevo" className="text-sm font-medium text-tinta">
                        Tipo de bloque
                    </label>
                    <select
                        id="tipo-bloque-nuevo"
                        value={tipoAAgregar}
                        onChange={(evento) => setTipoAAgregar(evento.target.value)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        {TIPOS_BLOQUE.map((meta) => (
                            <option key={meta.tipo} value={meta.tipo}>
                                {meta.etiqueta}
                            </option>
                        ))}
                    </select>
                </div>

                <Boton onClick={agregarBloque} className="flex items-center gap-2">
                    <Plus size={16} strokeWidth={1.75} />
                    Agregar bloque
                </Boton>
            </div>

            {bloques.length === 0 && <p className="text-sm text-texto-suave">Esta página todavía no tiene bloques.</p>}

            <div className="space-y-3">
                {bloques.map((bloque, indice) => {
                    const meta = metaDelTipo(bloque.type);
                    const Icono = meta.Icono;
                    const expandido = expandidos.has(indice);

                    return (
                        <Tarjeta key={indice} className="space-y-0">
                            <div className="flex items-center justify-between gap-3">
                                <button
                                    type="button"
                                    onClick={() => alternarExpandido(indice)}
                                    className="flex flex-1 items-center gap-3 rounded-boton p-2 text-left hover:bg-fondo-panel"
                                >
                                    <Icono size={20} strokeWidth={1.75} className="text-texto-suave" />
                                    <span className="font-semibold text-tinta">{meta.etiqueta}</span>
                                    {expandido ? (
                                        <ChevronUp size={18} strokeWidth={1.75} className="text-texto-suave" />
                                    ) : (
                                        <ChevronDown size={18} strokeWidth={1.75} className="text-texto-suave" />
                                    )}
                                </button>

                                <div className="flex items-center gap-1">
                                    <button
                                        type="button"
                                        aria-label="Subir"
                                        disabled={indice === 0}
                                        onClick={() => moverBloque(indice, -1)}
                                        className="rounded-boton p-2 text-texto-suave hover:bg-fondo-panel disabled:opacity-30"
                                    >
                                        <ChevronUp size={16} strokeWidth={1.75} />
                                    </button>
                                    <button
                                        type="button"
                                        aria-label="Bajar"
                                        disabled={indice === bloques.length - 1}
                                        onClick={() => moverBloque(indice, 1)}
                                        className="rounded-boton p-2 text-texto-suave hover:bg-fondo-panel disabled:opacity-30"
                                    >
                                        <ChevronDown size={16} strokeWidth={1.75} />
                                    </button>
                                    <button
                                        type="button"
                                        aria-label="Eliminar bloque"
                                        onClick={() => quitarBloque(indice)}
                                        className="rounded-boton p-2 text-texto-suave hover:bg-fondo-panel"
                                    >
                                        <Trash2 size={16} strokeWidth={1.75} />
                                    </button>
                                </div>
                            </div>

                            {expandido && (
                                <div className="mt-4 border-t border-gris-claro pt-4">
                                    <FormularioBloque
                                        slug={slug}
                                        tipo={bloque.type}
                                        datos={bloque.data}
                                        onChange={(datos) => cambiarDatosBloque(indice, datos)}
                                    />
                                </div>
                            )}
                        </Tarjeta>
                    );
                })}
            </div>
        </div>
    );
}
