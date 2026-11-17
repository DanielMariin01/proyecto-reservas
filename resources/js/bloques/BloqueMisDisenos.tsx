import { useState } from 'react';
import type { ReactElement } from 'react';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

interface DisenoItem {
    imagen?: unknown;
    titulo?: unknown;
    descripcion?: unknown;
}

interface Diseno {
    imagen: string;
    titulo: string;
    descripcion: string;
}

function leerDisenos(items: unknown): Diseno[] {
    if (!Array.isArray(items)) {
        return [];
    }

    return (items as DisenoItem[])
        .filter((item): item is DisenoItem & { imagen: string } => typeof item.imagen === 'string')
        .map((item) => ({
            imagen: item.imagen,
            titulo: typeof item.titulo === 'string' ? item.titulo : '',
            descripcion: typeof item.descripcion === 'string' ? item.descripcion : '',
        }));
}

export default function BloqueMisDisenos({ datos }: PropiedadesBloque): ReactElement {
    const [disenoAmpliado, setDisenoAmpliado] = useState<Diseno | null>(null);
    const disenos = leerDisenos(datos.items);

    if (disenos.length === 0) {
        return <></>;
    }

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-5xl px-4 py-10 sm:py-16">
                <h2 className="mb-6 text-3xl font-bold sm:text-4xl">Mis diseños</h2>

                <div className="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
                    {disenos.map((diseno, indice) => (
                        <button
                            key={indice}
                            type="button"
                            onClick={() => setDisenoAmpliado(diseno)}
                            className="flex flex-col items-center gap-2 text-center"
                        >
                            <span className="aspect-square w-full overflow-hidden rounded-boton transition-opacity hover:opacity-90">
                                <img src={diseno.imagen} alt="" className="h-full w-full object-cover" />
                            </span>
                            {diseno.titulo && <p className="font-semibold">{diseno.titulo}</p>}
                            {diseno.descripcion && <p className="line-clamp-2 text-sm opacity-80">{diseno.descripcion}</p>}
                        </button>
                    ))}
                </div>

                {disenoAmpliado && (
                    <div
                        className="fixed inset-0 z-50 flex items-center justify-center bg-tinta/80 p-4"
                        onClick={() => setDisenoAmpliado(null)}
                        role="presentation"
                    >
                        <div className="max-h-[90vh] max-w-full overflow-hidden rounded-tarjeta bg-blanco">
                            <img src={disenoAmpliado.imagen} alt="" className="max-h-[70vh] w-full object-contain" />
                            {(disenoAmpliado.titulo || disenoAmpliado.descripcion) && (
                                <div className="p-4 text-center">
                                    {disenoAmpliado.titulo && <p className="font-semibold text-tinta">{disenoAmpliado.titulo}</p>}
                                    {disenoAmpliado.descripcion && <p className="mt-1 text-sm text-texto-suave">{disenoAmpliado.descripcion}</p>}
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
}
